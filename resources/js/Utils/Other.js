export const generateOrderId = () => {
  const prefix = "TUK";

  // Retrieve the last order ID from localStorage
  const lastOrderId = localStorage.getItem("lastOrderId");

  let count = 1; // Start with 1 by default
  if (lastOrderId) {
    count = parseInt(lastOrderId, 10) + 1;
  }

  // Format the count with leading zeros (e.g., "0001")
  const formattedCount = String(count).padStart(4, "0");

  // Save the new count in localStorage
  localStorage.setItem("lastOrderId", count);

  // Return the generated order ID without date
  return `${prefix}/${formattedCount}`;
};
