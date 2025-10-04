<template>
  <TransitionRoot as="template" :show="open" static>
    <Dialog class="relative z-10" static>
      <!-- Modal Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click.stop />
      </TransitionChild>

      <!-- Modal Content -->
      <div class="fixed inset-0 z-10 flex items-center justify-center">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel
            class="bg-white border-4 border-blue-600 rounded-[20px] shadow-xl max-w-xl w-full p-6 text-center"
          >
            <DialogTitle class="text-5xl font-bold">Payment Successful!</DialogTitle>

            <div class="w-full h-full flex flex-col justify-center items-center space-y-8 mt-4">
              <p class="text-justify text-3xl text-black">Order Payment is Successful!</p>
              <div>
                <img src="/images/checked.png" class="h-24 object-cover w-full" />
              </div>
            </div>

            <div class="flex justify-center items-center space-x-4 pt-4 mt-4">
              <p
                @click="handlePrintReceipt"
                class="cursor-pointer bg-blue-600 text-white font-bold uppercase tracking-wider px-4 shadow-xl py-4 rounded-xl"
              >
                Print Receipt
              </p>
              <!--
              <p
                @click="handleKOTPrintReceipt"
                class="cursor-pointer bg-orange-600 text-white font-bold uppercase tracking-wider px-4 shadow-xl py-4 rounded-xl"
              >
                KOT Receipt
              </p>
              -->
              <p
                @click="$emit('update:open', false)"
                class="cursor-pointer bg-red-600 text-white font-bold uppercase tracking-wider px-4 shadow-xl rounded py-4 rounded-xl"
              >
                Close
              </p>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { ref, computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const emit = defineEmits(["update:open"]);

const page = usePage();
const companyInfo = computed(() => page.props.companyInfo);

// Props
const props = defineProps({
  open: { type: Boolean, required: true },
  products: { type: Array, required: true },
  selectedTable: { type: Object, required: true },
  cashier: Object,
  customer: Object,
  orderId: String,
  balance: Number,
  cash: Number,
  subTotal: String,
  totalDiscount: String,
  total: String,
    custom_discount: Number,
  custom_discount_type: String,
  kitchen_note: String,
  delivery_charge: String,
  bank_service_charge: String,
  service_charge: String,
  order_type: String,
  selectedPaymentMethod: String,


  owner_discount_value: { type: Number, default: 0 },
  owner_code: { type: String, default: null },

  // NEW: bind from parent to decide USD vs LKR
  isConvertPrice: { type: Boolean, default: false },
});


 let totalDiscountValue = 0;
    if (Number(props.custom_discount || 0)) {
      if (props.custom_discount_type === 'percent') {
        // If percentage, calculate from subtotal
        const subTotalNum = Number(props.subTotal || 0);
        totalDiscountValue = (subTotalNum * Number(props.custom_discount)) / 100;
      } else {
        // If fixed amount
        totalDiscountValue = Number(props.custom_discount);
      }
      // Add owner discount
      totalDiscountValue += Number(props.owner_discount_value || 0);
    }

// currency helpers
const C = computed(() => (props.isConvertPrice ? "USD" : "LKR"));
const fmt = (n) => Number(n || 0).toFixed(2);

// Receipt printer (USD/LKR aware)
const handlePrintReceipt = () => {
  // Build product rows using current selling_price (already swapped in parent)
  const productRows = props.products
    .map((p) => {
      const unitPrice = Number(
        p.discount > 0 && p.apply_discount ? p.discounted_price : p.selling_price
      );
      const line = unitPrice * Number(p.quantity);
      return `
        <tr>
          <td>
            ${p.name}
            <br><span style="font-size: 8px; font-weight: bold; font-style: italic;">
              (${fmt(p.selling_price)} ${C.value} ${p.discount > 0 && p.apply_discount ? ` • ${p.discount}% off` : ""})
            </span>
          </td>
          <td style="text-align:center;">${p.quantity}</td>
          <td style="text-align:right;">${fmt(line)} ${C.value}</td>
        </tr>
      `;
    })
    .join("");


    const totalDiscountValue =
    Number(props.totalDiscount || 0) + Number(props.custom_discount || 0);
  
const maybeSubTotal = Number(props.subTotal || 0)
  ? `<div><span>Sub Total</span><span>${fmt(props.subTotal)} ${C.value}</span></div>`
  : "";
  
  const maybeOwner = Number(props.owner_discount_value) !== 0
    ? `<div><span>Owner Discount ${props.owner_code ? `(${props.owner_code})` : ""}</span><span>(${fmt(props.owner_discount_value)}) ${C.value}</span></div>`
    : "";



  const maybeDelivery = props.delivery_charge
    ? `<div><span>Delivery Charge</span><span>${fmt(props.delivery_charge)} ${C.value}</span></div>`
    : "";

  const maybeService = props.service_charge
    ? `<div><span>Service Charge</span><span>${fmt(props.service_charge)} %</span></div>`
    : "";

  const maybeBank = props.bank_service_charge
    ? `<div><span>Bank Service Charge</span><span>${fmt(props.bank_service_charge)} %</span></div>`
    : "";

  const maybeTotal = Number(props.total) !== 0
    ? `<div style="font-weight:bold;"><span>Total</span><span>${fmt(props.total)} ${C.value}</span></div>`
    : "";

  const maybeCash = Number(props.cash) !== 0
    ? `<div><span>Cash</span><span>${fmt(props.cash)} ${C.value}</span></div>`
    : "";

  const maybeBalance = Number(props.balance) !== 0
    ? `<div style="font-weight:bold;"><span>Balance</span><span>${fmt(props.balance)} ${C.value}</span></div>`
    : "";


    const maybeCustDisc = totalDiscountValue > 0
      ? `
        <div style="
          display:flex;
          justify-content:space-between;
          align-items:center;
          margin:8px 0;
          padding:10px 5px;
          border:1px solid #000;    
          font-size:12px;
          border-radius:0px;
          font-weight:700;
        ">
          <span style="font-size:12px;letter-spacing:.02em;">TOTAL DISCOUNT</span>
          <span style="font-size:12px;">
            ${fmt(totalDiscountValue)} ${C.value}
          </span>
        </div>
      `
      : '';


  const orderType =
    props.order_type === "takeaway"
      ? "Takeaway"
      : props.order_type === "pickup"
      ? "Delivery"
      : "Dine In";

  const receiptHTML = `
  <!doctype html>
  <html>
  <head>
    <meta charset="utf-8" />
    <title>Receipt</title>
    <style>
      @media print {
        body { margin:0; padding:0; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
      }
      body { background:#fff; font-size:12px; font-family:Arial,sans-serif; margin:0; padding:10px; color:#000; }
      .header { text-align:center; margin-bottom:16px; }
      .header h1 { font-size:20px; font-weight:bold; margin:0; }
      .header p { font-size:10px; margin:4px 0; }
      .section { margin: 8px 0 16px; }
      .info-row { display:flex; justify-content:space-between; font-size:12px; margin-top:8px; }
      .info-row p { margin:0; font-weight:bold; }
      .info-row small { font-weight:normal; }
      table { width:100%; font-size:12px; border-collapse:collapse; margin-top:8px; }
      th, td { padding:6px 8px; }
      th { text-align:left; }
      td { text-align:right; }
      td:first-child { text-align:left; }
      .badge { font-weight:bold; border:1px solid #000; text-align:center; padding:5px; margin:8px 0; }
      .totals { border-top:1px solid #000; padding-top:8px; font-size:12px; }
      .totals div { display:flex; justify-content:space-between; margin-bottom:8px; }
      .footer { text-align:center; font-size:10px; margin-top:16px; }
      .footer p { margin:6px 0; }
      .italic { font-style:italic; }
    </style>
  </head>
  <body>
    <div class="receipt-container">
      <div class="header">
        <img src="/images/billlog.png" style="width:200px; height:100px;" />
        ${companyInfo?.value?.name ? `<h1>${companyInfo.value.name}</h1>` : ""}
        ${companyInfo?.value?.address ? `<p>${companyInfo.value.address}</p>` : ""}
        ${
          companyInfo?.value?.phone || companyInfo?.value?.phone2 || companyInfo?.value?.email
            ? `<p>${companyInfo.value.phone || ""} ${companyInfo.value.phone2 ? " | " + companyInfo.value.phone2 : ""} ${companyInfo.value.email ? " | " + companyInfo.value.email : ""}</p>`
            : ""
        }
      </div>

      <div class="section">
        <div class="info-row">
          <div><p>Date:</p><small>${new Date().toLocaleDateString()}</small></div>
          <div><p>Order No:</p><small>${props.orderId}</small></div>
        </div>
        <div class="info-row">
          <div><p>Customer:</p><small>${props.customer?.name || ""}</small></div>
          <div><p>Cashier:</p><small>${props.cashier?.name || ""}</small></div>
        </div>
        <div class="info-row">
          <div><p>Payment Type:</p><small>${props.selectedPaymentMethod}</small></div>
          <div><p>Currency:</p><small>${C.value}</small></div>
        </div>
        <div class="badge"><small>Order Type: ${orderType}</small></div>
      </div>

      <div class="section">
        <table>
          <thead>
            <tr>
              <th>Items</th>
              <th style="text-align:center;">Qty</th>
              <th style="text-align:right;">Price</th>
            </tr>
          </thead>
          <tbody>
            ${productRows}
          </tbody>
        </table>
      </div>

      <div class="totals">
      ${maybeSubTotal}
        ${maybeOwner}
  ${maybeDelivery}
  ${maybeService}
  ${maybeBank}
  ${maybeTotal}
  ${maybeCash}
  ${maybeBalance}
  ${maybeCustDisc}  
      </div>

      ${
        props.kitchen_note
          ? `<div style="font-weight:bold; text-align:left; border-top:1px solid #000; border-bottom:1px solid #000; padding:10px 0;">
               <small>Note: ${props.kitchen_note}</small>
             </div>`
          : ""
      }

      <div class="footer">
        <p>THANK YOU COME AGAIN</p>
        <p class="italic">Let the quality define its own standards</p>
        <p style="font-weight:bold;">Powered by JAAN Network Ltd.</p>
        <p>${new Date().toLocaleTimeString()}</p>
      </div>
    </div>
  </body>
  </html>
  `;

  const w = window.open("", "_blank");
  if (!w) return alert("Failed to open print window. Please check your browser settings.");
  w.document.open();
  w.document.write(receiptHTML);
  w.document.close();
  w.onload = () => { w.focus(); w.print(); w.close(); };
};

// Optional: KOT note (kept simple; currency not shown there)
const handleKOTPrintReceipt = () => {
  const productRows = props.products
    .map((p) => `<tr><td>${p.name}</td><td style="text-align:center;">${p.quantity}</td></tr>`)
    .join("");

  const orderType =
    props.order_type === "takeaway"
      ? "Takeaway"
      : props.order_type === "pickup"
      ? "Delivery"
      : "Dine In";

  const receiptHTML = `
  <!doctype html>
  <html>
  <head>
    <meta charset="utf-8" />
    <title>KOT</title>
    <style>
      @media print { body { margin:0; padding:0; -webkit-print-color-adjust:exact; print-color-adjust:exact; } }
      body { background:#fff; font-size:12px; font-family:Arial,sans-serif; margin:0; padding:10px; color:#000; }
      .info-row { display:flex; justify-content:space-between; font-size:12px; margin-top:8px; }
      .badge { font-weight:bold; border:1px solid #000; text-align:center; padding:5px; margin:8px 0; }
      table { width:100%; font-size:12px; border-collapse:collapse; margin-top:8px; }
      th, td { padding:6px 8px; }
      th { text-align:left; }
      td:first-child { text-align:left; }
      td:last-child { text-align:center; }
    </style>
  </head>
  <body>
    <h1 style="text-align:center;">KOT Note</h1>

    <div class="badge"><small>Order Type: ${orderType}</small></div>

    <div class="section">
      <div class="info-row">
        <div><p>Date:</p><small>${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</small></div>
        <div><p>Order No:</p><small>${props.orderId}</small></div>
      </div>
      <div class="info-row">
        <div><p>Customer:</p><small>${props.customer?.name || ""}</small></div>
        <div><p>Cashier:</p><small>${props.cashier?.name || ""}</small></div>
      </div>
    </div>

    <table>
      <thead>
        <tr><th>Product Name</th><th style="text-align:center;">Qty</th></tr>
      </thead>
      <tbody>${productRows}</tbody>
    </table>

    ${
      props.kitchen_note
        ? `<div style="font-weight:bold; text-align:left; border-top:1px solid #000; border-bottom:1px solid #000; padding:10px 0;">
             <small>Note: ${props.kitchen_note}</small>
           </div>`
        : ""
    }
  </body>
  </html>
  `;

  const w = window.open("", "_blank");
  if (!w) return alert("Failed to open print window. Please check your browser settings.");
  w.document.open();
  w.document.write(receiptHTML);
  w.document.close();
  w.onload = () => { w.focus(); w.print(); w.close(); };
};

</script>
