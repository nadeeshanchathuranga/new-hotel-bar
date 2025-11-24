<template>
  <Head title="KOT Reports" />
  <Banner />
  <div class="flex flex-col items-center min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
    <Header />

    <!-- Header & Filters -->
    <div class="w-full py-8 space-y-8">
      <div class="flex md:flex-row flex-col md:items-center items-start justify-between md:space-y-0 space-y-4">
        <div class="flex items-center space-x-4">
          <Link href="/"><img src="/images/back-arrow.png" class="w-14 h-14" /></Link>
          <p class="text-4xl font-bold text-black uppercase">KOT Reports</p>
        </div>

        <div date-rangepicker class="flex items-center space-x-4">
          <input v-model="startDate" type="date"
                 class="text-xl text-blue-600 bg-white border border-blue-300 rounded-lg p-2.5"
                 placeholder="Start Date" />
          <span class="text-xl font-bold text-blue-600">To</span>
          <input v-model="endDate" type="date"
                 class="text-xl text-blue-600 bg-white border border-blue-300 rounded-lg p-2.5"
                 placeholder="End Date" />

          <button @click="filterData"
                  class="px-6 py-3 text-xl text-white bg-blue-600 rounded-lg">
            Filter
          </button>
          <Link href="/kot_reports"
                class="px-6 py-3 text-xl text-white bg-blue-600 rounded-lg">
            Reset
          </Link>
        </div>
      </div>
    </div>

    <!-- Sales Table -->
    <div class="w-full bg-white border-4 border-black rounded-xl p-6">
      <div class="flex justify-between items-center pb-4">
        <h2 class="text-2xl font-semibold text-slate-700">KOT Sales Table</h2>
        <button @click="downloadSalesTableExcel"
          class="px-4 py-2 text-md font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-md">
          Download Excel
        </button>
      </div>

      <div class="overflow-x-auto overflow-y-auto max-h-[480px] border rounded-xl mt-2">
        <table class="w-full text-gray-800 bg-white border border-gray-300 rounded-lg shadow-md">
          <thead class="sticky top-0 z-10 bg-black text-white text-[14px]">
            <tr>
              <th class="p-3 text-center">#</th>
              <th class="p-3 text-center">Date</th>
              <th class="p-3 text-center">Order Number</th>
              <th class="p-3 text-left">Product Name</th>
              <th class="p-3 text-left">Category</th>
              <th class="p-3 text-center">Qty</th>
            </tr>
          </thead>

          <tbody class="text-[12px] font-medium">
            <template v-for="(s, saleIdx) in sales" :key="s.id ?? saleIdx">
              <tr v-for="(item, itemIdx) in (s.sale_items || [])"
                  :key="`${s.id}-${item.id || itemIdx}`"
                  class="border-b hover:bg-gray-100 transition">
                <td class="p-3 text-center">{{ getRowNumber(saleIdx, itemIdx) }}</td>
                <td class="p-3 text-center">{{ formatDate(s.sale_date) }}</td>
                <td class="p-3 text-center">{{ s.order_id }}</td>
                <td class="p-3">{{ item.product?.name || 'N/A' }}</td>
                <td class="p-3">{{ item.product?.category?.name || 'N/A' }}</td>
                <td class="p-3 text-center">{{ Number(item.quantity || 0) }}</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <Footer />
</template>

<script setup>
import { ref } from "vue";
import { Link, router, Head } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import * as XLSX from "xlsx";

const props = defineProps({
  sales: { type: Array, required: true },
  startDate: { type: String, default: "" },
  endDate: { type: String, default: "" },
});

const startDate = ref(props.startDate);
const endDate = ref(props.endDate);
const sales = ref(props.sales);

// Helpers
const formatDate = (d) => (d ? new Date(d).toLocaleDateString() : "");
const getRowNumber = (saleIdx, itemIdx) => {
  let count = 0;
  for (let i = 0; i < saleIdx; i++) {
    count += (sales.value[i].sale_items || []).length;
  }
  return count + itemIdx + 1;
};

// ✅ Filter function (will fix next step)
const filterData = () => {
  if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
    alert("Start date cannot be greater than end date.");
    return;
  }
  router.get(
    route("reports.kot_index"),
    { start_date: startDate.value, end_date: endDate.value },
    { preserveScroll: true, preserveState: false }
  );
};

const downloadSalesTableExcel = () => {
  // 1) Filter data by the selected range (unchanged)
  const filteredSales = sales.value.filter((sale) => {
    if (!startDate.value && !endDate.value) return true;
    const saleDate = new Date(sale.sale_date);
    const start = startDate.value ? new Date(startDate.value) : null;
    const end = endDate.value ? new Date(endDate.value) : null;

    if (start && end) return saleDate >= start && saleDate <= end;
    if (start) return saleDate >= start;
    if (end) return saleDate <= end;
    return true;
  });

  // 2) Build data rows (include Order Number)
  const header = ["#", "Date", "Order Number", "Product Name", "Category", "Qty"];
  const rows = [];
  let rowNum = 1;

  filteredSales.forEach((s) => {
    (s.sale_items || []).forEach((item) => {
      rows.push([
        rowNum++,
        new Date(s.sale_date),                          // Date (col B, index 1)
        s.order_id ?? s.orderNo ?? "N/A",               // Order Number (col C)
        item.product?.name || "N/A",                    // Product Name (col D)
        item.product?.category?.name || "N/A",          // Category (col E)
        Number(item.quantity ?? 0),                     // Qty (col F)
      ]);
    });
  });

  // 3) Create workbook & sheet
  const aoa = [header, ...rows];
  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(aoa);

  // 4) Ensure Date column (B) is typed/formatted as a date
  for (let r = 1; r < aoa.length; r++) {
    const cellRef = XLSX.utils.encode_cell({ r, c: 1 }); // column B (0-indexed)
    const cell = ws[cellRef];
    if (cell && cell.v instanceof Date && !isNaN(cell.v)) {
      cell.t = "d";
      cell.z = "yyyy-mm-dd";
    }
  }

  // 5) Column widths (now 6 columns)
  ws["!cols"] = [
    { wch: 6 },   // #
    { wch: 12 },  // Date
    { wch: 18 },  // Order Number
    { wch: 32 },  // Product Name
    { wch: 22 },  // Category
    { wch: 8 },   // Qty
  ];

  // 6) AutoFilter over the used range
  if (ws["!ref"]) {
    const range = XLSX.utils.decode_range(ws["!ref"]);
    ws["!autofilter"] = {
      ref: XLSX.utils.encode_range({
        s: { r: 0, c: 0 },
        e: { r: range.e.r, c: range.e.c },
      }),
    };
  }

  // Optional: freeze header
  // ws["!freeze"] = { rows: 1, cols: 0 };

  XLSX.utils.book_append_sheet(wb, ws, "KOT Sales");
  XLSX.writeFile(
    wb,
    `KOT_Sales_${startDate.value || "All"}_to_${endDate.value || "All"}.xlsx`
  );
};


</script>

<style scoped>
thead { position: sticky; top: 0; z-index: 10; }
</style>
