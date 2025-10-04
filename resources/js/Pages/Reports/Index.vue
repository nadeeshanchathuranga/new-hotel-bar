<template>
  <Head title="Reports" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
    <Header />

    <!-- Page header + date filters -->
    <div class="w-full py-12 space-y-16">
      <div class="flex md:flex-row flex-col md:items-center items-start justify-between md:space-y-0 space-y-4">
        <div class="flex items-center justify-center space-x-4">
          <Link href="/"><img src="/images/back-arrow.png" class="w-14 h-14" /></Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">Reports</p>
        </div>

        <div date-rangepicker class="flex items-center space-x-4">
          <div class="relative">
            <input v-model="startDate" type="date"
                   class="text-xl font-normal tracking-wider text-blue-600 bg-white border border-blue-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                   placeholder="Start Date" />
          </div>
          <span class="text-xl font-bold tracking-wider text-blue-600">To</span>
          <div class="relative">
            <input v-model="endDate" type="date"
                   class="text-xl font-normal tracking-wider text-blue-600 bg-white border border-blue-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                   placeholder="End Date" />
          </div>

          <button @click="filterData"
                  class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select hidden sm:inline-block">
            Filter
          </button>
          <Link href="/reports"
                class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select hidden sm:inline-block">
            Reset
          </Link>
        </div>

        <div class="w-full flex justify-center items-center space-x-4 md:hidden">
          <button @click="filterData"
                  class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select">
            Filter
          </button>
          <Link href="/reports"
                class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select">
            Reset
          </Link>
        </div>
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid w-full md:grid-cols-5 grid-cols-3 gap-4 mb-4">
      <div class="py-6 flex flex-col justify-center items-center border-2 border-[#EC6116] w-full space-y-4 rounded-2xl bg-[#EC611666] shadow-lg hover:-translate-y-1 transition">
        <div class="flex flex-col items-center justify-center">
          <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Total Sales</h2>
          <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Amount</h2>
        </div>
        <p class="text-2xl font-bold text-black">
          {{ finalSalesAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR
        </p>
      </div>
      <div class="py-6 flex flex-col justify-center items-center border-2 border-[#488D3F] w-full space-y-8 rounded-2xl bg-[#488D3F66] shadow-lg hover:-translate-y-1 transition">
        <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Net Profit</h2>
        <p class="text-2xl font-bold text-black">{{ salesProfitTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR</p>
      </div>
      <div class="py-6 flex flex-col justify-center items-center border-2 border-[#16D0EC] w-full space-y-4 rounded-2xl bg-[#16D0EC66] shadow-lg hover:-translate-y-1 transition">
        <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Total Discount</h2>
        <p class="text-2xl font-bold text-black">{{ totalDiscounts.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR</p>
      </div>
      <div class="py-6 flex flex-col justify-center items-center border-2 border-[#9E16EC] w-full space-y-4 rounded-2xl bg-[#9E16EC66] shadow-lg hover:-translate-y-1 transition">
        <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Number of Transactions</h2>
        <p class="text-2xl font-bold text-black">{{ totalTransactions }}</p>
      </div>
      <div class="py-6 flex flex-col justify-center items-center border-2 border-[#EC16D7] w-full space-y-4 rounded-2xl bg-[#EC16D766] shadow-lg hover:-translate-y-1 transition">
        <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Total Number of Customers</h2>
        <p class="text-2xl font-bold text-black">{{ totalCustomer }}</p>
      </div>
    </div>

    <!-- Sales Table -->
    <div class="w-full bg-white border-4 border-black rounded-xl p-6">
      <h2 class="text-2xl font-semibold text-slate-700 text-center pb-4">Sales Table</h2>

      <div class="flex justify-between items-center pb-4">
        <div class="flex gap-4">
          <!-- <button @click="downloadSalesTablePDF"
                  class="px-4 py-2 text-md font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-md">
            Download PDF
          </button> -->

          <button @click="downloadSalesTableExcel"
            class="px-4 py-2 text-md font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-md">
            Download Excel
          </button>
        </div>
        <div class="flex items-center gap-3">
          <div class="py-2 px-4 border-2 border-green-600 rounded-xl bg-green-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Total Qty:
              <span class="text-base font-bold">{{ salesTotalQty.toLocaleString() }}</span>
            </p>
          </div>
          <div class="py-2 px-4 border-2 border-blue-600 rounded-xl bg-blue-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Final Selling Price:
              <span class="text-base font-bold">
                {{ finalSalesAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR
              </span>
            </p>
          </div>
          <div class="py-2 px-4 border-2 border-yellow-600 rounded-xl bg-yellow-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Discounts:
              <span class="text-base font-bold">
                {{ totalDiscounts.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR
              </span>
            </p>
          </div>
          <div class="py-2 px-4 border-2 border-red-600 rounded-xl bg-red-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Profit:
              <span class="text-base font-bold">
                {{ salesProfitTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR
              </span>
            </p>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto overflow-y-auto max-h-[480px] border rounded-xl mt-2">
        <table id="salesTbl" class="sales-table w-full text-gray-800 bg-white border border-gray-300 rounded-lg shadow-md">
          <colgroup>
            <col style="width:48px" />
            <col style="width:110px" />
            <col style="width:170px" />
            <col style="width:200px" />
            <col style="width:70px" />
            <col style="width:140px" />
            <col style="width:120px" />
            <col style="width:160px" />
            <col style="width:160px" />
            <col style="width:140px" />
            <col style="width:160px" />
            <col style="width:140px" />
          </colgroup>

          <thead class="sticky top-0 z-10">
            <tr class="bg-gradient-to-r from-slate-700 via-slate-600 to-slate-700 text-white text-[14px] border-b border-slate-800">
              <th class="p-3 text-left font-semibold">#</th>
              <th class="p-3 text-left font-semibold">Date</th>
              <th class="p-3 text-left font-semibold">Order Number</th>
              <th class="p-3 text-left font-semibold">Customer</th>
              <th class="p-3 text-center font-semibold">Dish Qty</th>
              <th class="p-3 num font-semibold">Total Price</th>
              <th class="p-3 num font-semibold">Service Charge (%)</th>
              <th class="p-3 num font-semibold">Price with Service</th>
              <th class="p-3 num font-semibold">Customer Discounts</th>
              <th class="p-3 num font-semibold">Owner</th>
              <th class="p-3 num font-semibold">Owner Discount</th>
              <th class="p-3 num font-semibold">Profit</th>
            </tr>
          </thead>

          <tbody class="text-[12px] font-medium">
            <tr v-for="(s, i) in sales" :key="s.id ?? i" class="border-b transition duration-200 hover:bg-gray-100">
              <td class="p-3 text-center">{{ i + 1 }}</td>
              <td class="p-3 whitespace-nowrap text-center">{{ formatDate(s.sale_date) }}</td>
              <td class="p-3 text-center">{{ s.order_id ? s.order_id : 'Service -' }} {{ s.service_name }}</td>
              <td class="p-3">{{ s.customer?.name ?? 'N/A' }}</td>
              <td class="p-3 text-center">{{ saleQty(s) }}</td>
              <td class="p-3 num text-center">{{ toMoney(Number(s.total_amount || 0)) }}</td>
              <td class="p-3 num text-center">{{ Number(s.service_charge || 0).toFixed(2) }}%</td>
              <td class="p-3 num text-right">{{ toMoney(priceWithService(s)) }}</td>
              <td class="p-3 num text-center">{{ toMoney(customerDiscountAmount(s)) }}</td>
              <td class="p-3 text-right">{{ (s.owner_discount_value && s.owner_discount_value != 0) ? (s.owner?.name ?? '—') : '—' }}</td>
              <td class="p-3 num text-center">{{ (s.owner_discount_value && s.owner_discount_value != 0) ? toMoney(s.owner_discount_value) : '—' }}</td>
              <td class="p-3 num text-center">
                {{ (Number(s.total_amount ?? 0) - Number(s.total_cost ?? 0)).toFixed(2) }}
              </td>
            </tr>
          </tbody>

          <tfoot class="bg-gray-50 text-[12px] font-semibold">
            <tr>
              <td class="p-3 text-right" colspan="4">Totals:</td>
              <td class="p-3 text-center">{{ salesTotalQty.toLocaleString() }}</td>
              <td class="p-3 num text-center">{{ salesGrossTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
              <td class="p-3 num text-center">—</td>
              <td class="p-3 num text-center">{{ salesWithServiceTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
              <td class="p-3 num text-center">{{ salesCustomerDiscountTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
              <td class="p-3 num text-center">—</td>
              <td class="p-3 num text-center">{{ salesOwnerDiscountTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
              <td class="p-3 num text-center">{{ salesProfitTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Charts -->
    <div class="flex md:flex-row flex-col items-center justify-center w-full h-full md:space-x-4 md:space-y-0 space-y-4">
      <div class="flex flex-col justify-between items-center md:w-1/3 w-full bg-white border-4 border-black rounded-xl h-[450px]">
        <div class="chart-container w-full p-4">
          <div class="w-full flex justify-between items-center md:pt-8">
            <h2 class="text-2xl font-medium tracking-wide text-slate-700 text-left">Employee Sales</h2>
            <button @click="downloadEmployeeSalesPDF" class="px-4 py-2 text-md font-normal tracking-wider text-white bg-orange-600 rounded-lg custom-select hover:bg-orange-700 hover:shadow-lg">Download PDF</button>
          </div>
          <div class="w-full h-full flex justify-center items-center">
            <Doughnut :data="chartData4" :options="chartOptions4" />
          </div>
        </div>
      </div>

      <div class="flex flex-col justify-between items-center md:w-1/3 w-full bg-white border-4 border-black rounded-xl h-[450px]">
        <div class="chart-container w-full p-4">
          <div class="w-full flex justify-between items-center md:pt-8">
            <h2 class="text-2xl font-medium tracking-wide text-slate-700 text-left">Product Sales</h2>
            <button @click="downloadProductQtyPDF" class="px-4 py-2 text-md font-normal tracking-wider text-white bg-orange-600 rounded-lg custom-select hover:bg-orange-700 hover:shadow-lg">Download PDF</button>
          </div>
          <Pie :data="chartData" :options="chartOptions" />
        </div>
      </div>

      <div class="flex flex-col justify-between items-center md:w-1/3 w-full bg-white border-4 border-black rounded-xl h-[450px]">
        <div class="chart-container w-full p-4">
          <div class="w-full flex justify-between items-center md:pt-8">
            <h2 class="text-2xl font-medium tracking-wide text-slate-700 text-left">Payment Methods</h2>
            <button @click="downloadPaymentMethodPDF" class="px-4 py-2 text-md font-normal tracking-wider text-white bg-orange-600 rounded-lg custom-select hover:bg-orange-700 hover:shadow-lg">Download PDF</button>
          </div>
          <Doughnut :data="chartData1" :options="chartOptions1" />
        </div>
      </div>
    </div>

    <!-- Top Products Stock Table -->
    <div class="w-full bg-white border-4 border-black rounded-xl p-6">
      <h2 class="text-2xl font-semibold text-slate-700 text-center pb-4">Top Products Stock Table</h2>
      <div class="flex justify-between items-center pb-4">
        <div class="flex gap-4">
          <button @click="downloadStockTablePDF" class="px-4 py-2 text-md font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-md">Download PDF</button>
        </div>
        <div class="flex items-center gap-3">
          <div class="py-2 px-4 border-2 border-green-600 rounded-xl bg-green-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Total Sales Qty:
              <span class="text-base font-bold">{{ totalSalesQty.toLocaleString() }}</span>
            </p>
          </div>
          <div class="py-2 px-4 border-2 border-blue-600 rounded-xl bg-blue-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Total Profit:
              <span class="text-base font-bold">{{ grandTotalProfit.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR</span>
            </p>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto max-h-[420px] border rounded-xl mt-2">
        <table id="stockQtyTbl" class="w-full text-gray-800 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
          <thead>
            <tr class="bg-gradient-to-r from-blue-700 via-blue-600 to-blue-700 text-white text-[14px] border-b border-blue-800">
              <th class="p-3 text-left font-semibold">#</th>
              <th class="p-3 text-left font-semibold">Product</th>
              <th class="p-3 text-center font-semibold">Sales Qty</th>
              <th class="p-3 text-center font-semibold">Total Sales Value (LKR)</th>
              <th class="p-3 text-center font-semibold">Price (LKR)</th>
              <th class="p-3 text-center font-semibold">Discount</th>
              <th class="p-3 text-center font-semibold">Price After Discount</th>
              <th class="p-3 text-center font-semibold">Profit</th>
            </tr>
          </thead>
          <tbody class="text-[12px] font-medium">
            <tr v-for="(p, i) in products" :key="p.id ?? i" class="border-b transition duration-200 hover:bg-gray-100">
              <td class="p-3 text-center">{{ i + 1 }}</td>
              <td class="p-3 font-bold">{{ p.name || 'N/A' }}</td>
              <td class="p-3 text-center">{{ Number(p.sales_qty || 0) }}</td>
              <td class="p-3 text-center">{{ (Number(p.sales_qty || 0) * Number(p.selling_price || 0)).toFixed(2) }}</td>
              <td class="p-3 text-center">{{ Number(p.selling_price || 0).toFixed(2) }}</td>
              <td class="p-3 text-center">
                <span v-if="Number(p.discount || 0) <= 100">{{ Number(p.discount || 0) }}%</span>
                <span v-else>Rs. {{ Number(p.discount).toFixed(2) }}</span>
              </td>
              <td class="p-3 text-center">{{ priceAfterDiscount(p).toFixed(2) }}</td>
              <td class="p-3 text-center">{{ totalProfit(p).toFixed(2) }} LKR</td>
            </tr>
          </tbody>
          <tfoot class="bg-gray-50 text-[12px] font-semibold">
            <tr>
              <td class="p-3 text-right" colspan="2">Totals:</td>
              <td class="p-3 text-right">{{ totalSalesQty.toLocaleString() }}</td>
              <td class="p-3 text-right"></td>
              <td class="p-3 text-right"></td>
              <td class="p-3 text-right"></td>
              <td class="p-3 text-right"></td>
              <td class="p-3 text-right">{{ grandTotalProfit.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

  </div>
  <Footer />
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Doughnut, Pie } from "vue-chartjs";
import { Link, router, Head } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import jsPDF from "jspdf";
import * as XLSX from "xlsx";
import autoTable from "jspdf-autotable";

import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  CategoryScale,
  LinearScale,
  BarElement,
} from "chart.js";

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale, BarElement);

// Props
const props = defineProps({
  products: { type: Array, required: true },
  sales: { type: Array, required: true },
  ownersList: { type: Array, required: true },
  totalSaleAmount: { type: Number, required: true },
  averageTransactionValue: { type: Number, required: true },
  netProfit: { type: Number, required: true },
  totalTransactions: { type: Number, required: true },
  totalDiscountLkr: { type: Number, required: true },
  totalCustomDiscountLkr: { type: Number, required: true },
  totalCustomer: { type: Number, required: true },
  startDate: { type: String, default: "" },
  endDate: { type: String, default: "" },
  categorySales: { type: Object, required: true },
  employeeSalesSummary: { type: Object, required: true },
  stockTransactionsReturn: { type: Array, default: () => [] },
});

// State
const startDate = ref(props.startDate);
const endDate = ref(props.endDate);
const products = ref(props.products);
const sales = ref(props.sales);

// ---------- Shared helpers ----------
const safe = (s) => String(s).replace(/[^\dA-Za-z-]/g, "_");

const toMoney = (n) => (Number(n || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatDate = (d) => (d ? new Date(d).toLocaleDateString() : "");

// Sales table calculations
const saleQty = (s) => (Array.isArray(s.sale_items) ? s.sale_items.reduce((n, it) => n + Number(it.quantity || 0), 0) : 0);

const customerDiscountAmount = (s) => {
  const gross = Number(s.total_amount || 0);
  const val = Number(s.custom_discount || 0);
  const type = s.custom_discount_type || "fixed";
  return type === "percent" ? (gross * val) / 100 : val;
};

const priceWithService = (s) => {
  const gross = Number(s.total_amount || 0);
  const svc = (gross * Number(s.service_charge || 0)) / 100;
  return gross + svc;
};

const saleProfit = (s) => {
  const gross = Number(s.total_amount || 0);
  const svc = (gross * Number(s.service_charge || 0)) / 100;
  const customerDisc = customerDiscountAmount(s);
  const ownerDisc = Number(s.owner_discount_value || 0);
  const finalPrice = Math.max(0, gross + svc - customerDisc - ownerDisc);
  const cost = Number(s.total_cost || 0);
  return finalPrice - cost;
};

// Sales totals
const salesTotalQty = computed(() => (sales.value || []).reduce((a, s) => a + saleQty(s), 0));

const salesGrossTotal = computed(() => 
  (sales.value || []).reduce((a, s) => a + Number(s.total_amount || 0), 0)
);

const salesWithServiceTotal = computed(() => 
  (sales.value || []).reduce((a, s) => a + priceWithService(s), 0)
);

const salesCustomerDiscountTotal = computed(() => 
  (sales.value || []).reduce((a, s) => a + customerDiscountAmount(s), 0)
);

const salesOwnerDiscountTotal = computed(() => 
  (sales.value || []).reduce((sum, s) => sum + Number(s.owner_discount_value || 0), 0)
);

const totalDiscounts = computed(() => 
  salesCustomerDiscountTotal.value + salesOwnerDiscountTotal.value
);

const finalSalesAmount = computed(() => 
  Math.max(0, salesWithServiceTotal.value - salesCustomerDiscountTotal.value - salesOwnerDiscountTotal.value)
);

// Note: This mirrors the table's Profit column: (total_amount - total_cost)
const salesProfitTotal = computed(() =>
  (sales.value || []).reduce(
    (sum, s) => sum + (Number(s.total_amount ?? 0) - Number(s.total_cost ?? 0)),
    0
  )
);

// Product table calculations
const priceAfterDiscount = (product) => {
  const price = Number(product.selling_price || 0);
  const discount = Number(product.discount || 0);
  return discount <= 100 ? price * (1 - discount / 100) : price - discount;
};

const profitPerUnit = (product) => priceAfterDiscount(product) - Number(product.cost_price || 0);
const totalProfit = (product) => profitPerUnit(product) * Number(product.sales_qty || 0);

const totalSalesQty = computed(() => products.value.reduce((s, p) => s + Number(p.sales_qty || 0), 0));
const grandTotalProfit = computed(() => products.value.reduce((s, p) => s + totalProfit(p), 0));

// Date filter
const filterData = () => {
  if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
    alert("Start date cannot be greater than end date.");
    return;
  }
  router.get(
    route("reports.index"),
    { start_date: startDate.value, end_date: endDate.value },
    { preserveScroll: true, preserveState: false }
  );
};

// Charts
const sortDescending = (data) => 
  Object.entries(data).sort((a, b) => b[1] - a[1]).reduce((acc, [k, v]) => ((acc[k] = v), acc), {});

const productQuantities = computed(() => {
  const quantities = {};
  (props.sales || []).forEach((sale) => {
    (sale.sale_items || []).forEach((item) => {
      const name = item.product && item.product.name ? item.product.name : "N/A";
      quantities[name] = (quantities[name] || 0) + Number(item.quantity || 0);
    });
  });
  return sortDescending(quantities);
});

const chartData = computed(() => ({
  labels: Object.keys(productQuantities.value),
  datasets: [{
    data: Object.values(productQuantities.value),
    backgroundColor: [
      "#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#9966FF", "#28a745",
      "#ffc107", "#17a2b8", "#e83e8c", "#fd7e14", "#6610f2", "#6f42c1",
      "#dc3545", "#adb5bd", "#20c997", "#ffc93c", "#6a0572", "#8ac926",
      "#ff595e", "#198754"
    ],
  }],
}));

const chartOptions = { 
  responsive: true, 
  maintainAspectRatio: false,
  plugins: { 
    legend: { display: true, position: "bottom" } 
  } 
};

const paymentMethodTotals = computed(() => {
  const totals = {};
  (props.sales || []).forEach((s) => {
    const m = s.payment_method || "N/A";
    totals[m] = (totals[m] || 0) + (parseFloat(s.total_amount) || 0);
  });
  return sortDescending(totals);
});

const chartData1 = computed(() => ({
  labels: Object.keys(paymentMethodTotals.value),
  datasets: [{
    data: Object.values(paymentMethodTotals.value),
    backgroundColor: [
      "#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#9966FF", "#28a745",
      "#ffc107", "#17a2b8", "#e83e8c", "#fd7e14", "#6610f2", "#6f42c1",
      "#dc3545", "#adb5bd", "#20c997", "#ffc93c", "#6a0572", "#8ac926",
      "#198754"
    ],
  }],
}));

const chartOptions1 = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: true, position: "bottom" },
    tooltip: { callbacks: { label: (c) => `LKR ${(+c.raw || 0).toLocaleString()}` } },
  },
};

const sortedEmployeeSales = computed(() =>
  Object.fromEntries(
    Object.entries(props.employeeSalesSummary).sort(([, a], [, b]) => 
      b["Total Sales Amount"] - a["Total Sales Amount"]
    )
  )
);

const chartData4 = computed(() => ({
  labels: Object.keys(sortedEmployeeSales.value),
  datasets: [{
    data: Object.values(sortedEmployeeSales.value).map(entry => entry["Total Sales Amount"] || 0),
    backgroundColor: [
      "#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#9966FF", "#28a745",
      "#ffc107", "#17a2b8", "#e83e8c", "#fd7e14", "#6610f2", "#6f42c1",
      "#dc3545", "#adb5bd", "#20c997", "#ffc93c", "#6a0572", "#8ac926",
      "#ff595e", "#198754"
    ],
  }],
}));

const chartOptions4 = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: true, position: "bottom" },
    tooltip: { callbacks: { label: (c) => `LKR ${(+c.raw).toLocaleString()}` } },
  },
};

// Date range label for PDFs/filenames
const dateRangeLabel = computed(() => {
  const s = startDate.value ? new Date(startDate.value).toLocaleDateString() : "All";
  const e = endDate.value ? new Date(endDate.value).toLocaleDateString() : "All";
  return `${s} — ${e}`;
});

// ---------- XLSX: Sales Table Export ----------
const downloadSalesTableExcel = () => {
  const header = [
    "#","Date","Order Number","Customer","Dish Qty",
    "Total Price","Service Charge (%)","Price with Service",
    "Customer Discounts","Owner","Owner Discount","Profit"
  ];

  const rows = (sales.value || []).map((s, i) => {
    const qty = saleQty(s);
    const total = Number(s.total_amount || 0);
    const svcPct = Number(s.service_charge || 0);
    const priceWSvc = priceWithService(s);
    const custDisc = customerDiscountAmount(s);
    const owner = (s.owner_discount_value && s.owner_discount_value != 0) ? (s.owner?.name ?? "—") : "—";
    const ownerDisc = Number(s.owner_discount_value || 0);
    // Match the table Profit column (gross: total - cost)
    const profit = Number(s.total_amount ?? 0) - Number(s.total_cost ?? 0);

    return [
      i + 1,
      formatDate(s.sale_date),
      s.order_id ? s.order_id : `Service - ${s.service_name || ""}`,
      s.customer?.name ?? "N/A",
      qty,
      total,
      svcPct,            // numeric; header clarifies it's %
      priceWSvc,
      custDisc,
      owner,
      ownerDisc,
      profit,
    ];
  });

  
  const aoa = [header, ...rows];

  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(aoa);

  // Auto column widths
  const widths = header.map((h, c) => {
    const contentLens = aoa.map(r => (r[c] == null ? 0 : String(r[c]).length));
    const maxLen = Math.max(h.length, ...contentLens);
    return { wch: Math.min(Math.max(maxLen + 2, 12), 40) };
  });
  ws['!cols'] = widths;

  // Number formats for money/qty/percent-like
  const formatCols = {
    qty: 4,
    money: [5, 7, 8, 10, 11],
    percent: 6,
  };
  const range = XLSX.utils.decode_range(ws['!ref']);

  // Apply formats to data rows (skip header at R=0)
  for (let R = 1; R <= range.e.r; R++) {
    // qty
    const qCell = ws[XLSX.utils.encode_cell({ r: R, c: formatCols.qty })];
    if (qCell && typeof qCell.v === "number") qCell.z = "0";

    // money columns
    for (const C of formatCols.money) {
      const cell = ws[XLSX.utils.encode_cell({ r: R, c: C })];
      if (cell && typeof cell.v === "number") cell.z = "0.00";
    }

    // percent column (kept as number like 5 -> 5.00)
    const pCell = ws[XLSX.utils.encode_cell({ r: R, c: formatCols.percent })];
    if (pCell && typeof pCell.v === "number") pCell.z = "0.00";
  }

  XLSX.utils.book_append_sheet(wb, ws, "Sales");
  XLSX.writeFile(wb, `Sales_Report_${safe(dateRangeLabel.value)}.xlsx`);
};

// ---------- PDF/CSV Exports ----------
const downloadEmployeeSalesPDF = () => {
  const doc = new jsPDF();
  doc.text("Top Employee Sales", 14, 10);
  const rows = Object.entries(sortedEmployeeSales.value).map(([employee, entry]) => [
    employee,
    (entry["Total Sales Amount"] || 0).toLocaleString()
  ]);
  autoTable(doc, { 
    head: [["Employee", "Total Sales Amount"]], 
    body: rows, 
    startY: 20 
  });
  doc.save("EmployeeSales.pdf");
};

const downloadProductQtyPDF = () => {
  const doc = new jsPDF();
  doc.text("Product Quantities", 14, 10);
  const rows = Object.entries(productQuantities.value).map(([product, qty]) => [product, qty]);
  autoTable(doc, { 
    head: [["Product Name", "Quantity"]], 
    body: rows, 
    startY: 20 
  });
  doc.save("ProductQuantities.pdf");
};

const downloadPaymentMethodPDF = () => {
  const doc = new jsPDF();
  doc.text("Payment Method Totals", 14, 10);
  const rows = Object.entries(paymentMethodTotals.value).map(([m, t]) => [
    m,
    `LKR ${Number(t || 0).toLocaleString()}`
  ]);
  autoTable(doc, { 
    head: [["Payment Method", "Total Amount"]], 
    body: rows, 
    startY: 20 
  });
  doc.save("PaymentMethodTotals.pdf");
};

const downloadSalesTableCSV = () => {
  const header = [
    "#","Date","Order Number","Customer","Owner","Qty",
    "Total Price (LKR)","Service Charge (%)","Customer Discounts (LKR)",
    "Owner Discount (LKR)","Profit (LKR)"
  ];
  const escapeCsv = (v) => {
    const s = String(v ?? "");
    return /[",\n]/.test(s) ? `"${s.replace(/"/g, '""')}"` : s;
  };
  const rows = (sales.value || []).map((s, i) => [
    i + 1,
    formatDate(s.sale_date),
    s.order_id ? s.order_id : `Service - ${s.service_name || ""}`,
    s.customer?.name ?? "N/A",
    (s.owner_discount_value && s.owner_discount_value != 0) ? (s.owner?.name ?? "—") : "—",
    saleQty(s),
    (+s.total_amount || 0).toFixed(2),
    (+s.service_charge || 0).toFixed(2),
    customerDiscountAmount(s).toFixed(2),
    (+s.owner_discount_value || 0).toFixed(2),
    saleProfit(s).toFixed(2),
  ]);
  const csv = [header, ...rows].map(r => r.map(escapeCsv).join(",")).join("\n");
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `Sales_Report_${safe(dateRangeLabel.value)}.csv`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
};

// const downloadSalesTablePDF = () => { /* ...kept as-is/commented... */ };

const downloadStockTablePDF = () => {
  const doc = new jsPDF("l", "mm", "a4");

  const rows = products.value.map((p, i) => [
    i + 1,
    p.name || "N/A",
    Number(p.sales_qty || 0).toString(),
    (Number(p.sales_qty || 0) * Number(p.selling_price || 0)).toFixed(2),
    Number(p.selling_price || 0).toFixed(2),
    Number(p.discount || 0) <= 100 ? `${Number(p.discount || 0)}%` : `Rs. ${Number(p.discount).toFixed(2)}`,
    priceAfterDiscount(p).toFixed(2),
    totalProfit(p).toFixed(2),
  ]);

  doc.setFontSize(16);
  doc.text("Top Products Stock Report", 14, 12);
  doc.setFontSize(10);
  doc.text(`Date range: ${dateRangeLabel.value} • Generated: ${new Date().toLocaleString()}`, 14, 18);

  const head = [[
    "#", "Product", "Sales Qty", "Total Sales Value (LKR)", 
    "Price (LKR)", "Discount", "Price After Discount", "Profit"
  ]];

  autoTable(doc, {
    head,
    body: rows,
    startY: 24,
    theme: "striped",
    styles: { fontSize: 9 },
    headStyles: { fillColor: [33, 102, 197], textColor: 255 },
    columnStyles: {
      0: { cellWidth: 10 },
      1: { cellWidth: 60 },
      2: { cellWidth: 22, halign: "right" },
      3: { cellWidth: 34, halign: "right" },
      4: { cellWidth: 28, halign: "right" },
      5: { cellWidth: 28, halign: "center" },
      6: { cellWidth: 34, halign: "right" },
      7: { cellWidth: 34, halign: "right" },
    },
    margin: { top: 18, left: 8, right: 8 },
  });

  

  autoTable(doc, {
    body: [totalsRow],
    startY: doc.lastAutoTable ? doc.lastAutoTable.finalY + 2 : 24,
    theme: "plain",
    styles: { fontSize: 10, fontStyle: "bold" },
    columnStyles: {
      0: { cellWidth: 10 },
      1: { cellWidth: 60, halign: "right" },
      2: { cellWidth: 22, halign: "right" },
      3: { cellWidth: 34 },
      4: { cellWidth: 28 },
      5: { cellWidth: 28 },
      6: { cellWidth: 34 },
      7: { cellWidth: 34, halign: "right" },
    },
    margin: { left: 8, right: 8 },
  });

  doc.save(`Top_Products_Stock_${safe(dateRangeLabel.value)}.pdf`);
};

// DataTables init
onMounted(() => {
  const jq = window.$;
  const $stock = jq && jq("#stockQtyTbl");
  if ($stock && jq.fn && jq.fn.dataTable) {
    if (jq.fn.dataTable.isDataTable($stock)) $stock.DataTable().destroy();
    const dt = $stock.DataTable({
      dom: "Bfrtip",
      paging: false,
      buttons: [],
      columnDefs: [{ targets: 0, searchable: false, orderable: false }],
      initComplete: function () {
        const $input = jq("div.dataTables_filter input");
        $input.attr("placeholder", "Search stock...");
        $input.on("keypress", function (e) {
          if (e.which == 13) dt.search(this.value).draw();
        });
      },
      language: { search: "" },
    });
  }
});
</script>

<style>
.dataTables_wrapper .dataTables_paginate { 
  display: flex; 
  justify-content: center; 
  align-items: center; 
  margin-top: 20px; 
}

#salesTbl_filter, #stockQtyTbl_filter, #expenseTbl_filter { 
  display: flex; 
  justify-content: flex-end; 
  align-items: center; 
  margin-bottom: 16px; 
  float: left; 
}

#salesTbl_filter label, #stockQtyTbl_filter label, #expenseTbl_filter label { 
  font-size: 17px; 
  color: #000000; 
  display: flex; 
  align-items: center; 
}

#salesTbl_filter input[type="search"], 
#stockQtyTbl_filter input[type="search"], 
#expenseTbl_filter input[type="search"] {
  font-weight: 400; 
  padding: 9px 15px; 
  font-size: 14px; 
  color: #000000cc; 
  border: 1px solid rgb(209 213 219); 
  border-radius: 5px; 
  background: #fff; 
  outline: none; 
  transition: all 0.5s ease;
}

#salesTbl_filter input[type="search"]:focus, 
#stockQtyTbl_filter input[type="search"]:focus, 
#expenseTbl_filter input[type="search"]:focus { 
  border: 1px solid #4b5563; 
  box-shadow: none; 
}

.dataTables_wrapper { 
  margin-bottom: 10px; 
}
</style>

<style scoped>
.chart-container { 
  display: flex; 
  flex-direction: column; 
  align-items: center; 
  justify-content: center; 
  width: 100%; 
  height: calc(100% - 50px); 
  position: relative; 
}

thead { 
  position: sticky; 
  top: 0; 
  z-index: 10; 
}

.max-h-64 { 
  max-height: 16rem; 
}

.num {
  text-align: right;
}
</style>
