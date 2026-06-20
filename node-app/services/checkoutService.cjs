const { findEventById } = require('../models/eventModel.cjs');
const {
  createOrder,
  updateOrderStatusByReference,
  decrementEventCapacity,
  incrementCouponUses,
  countRecentFreeOrders,
  sumPaidQuantityForEvent,
} = require('../models/orderModel.cjs');
const crypto = require('crypto');

function generateReference() {
  return 'PA_' + crypto.randomBytes(8).toString('hex');
}

function calculateQuote(event, quantity, couponCode = null, selectedTicketType = null) {
  const now = new Date();
  
  // Base/early-bird unit price or Custom Ticket Type
  let unitPrice = event.price ? Number(event.price) : 0;
  
  if (selectedTicketType && Array.isArray(event.ticket_types)) {
    for (const type of event.ticket_types) {
      if (type.name && String(type.name).toLowerCase() === String(selectedTicketType).toLowerCase()) {
        unitPrice = type.price ? Number(type.price) : 0;
        break;
      }
    }
  } else if (event.early_bird_price && event.early_bird_ends_at) {
    const earlyBirdEnd = new Date(event.early_bird_ends_at);
    if (now <= earlyBirdEnd) {
      unitPrice = Number(event.early_bird_price);
    }
  }
  
  const subtotalKobo = Math.round(unitPrice * quantity * 100);
  
  // Fees (never add fees for free events)
  let feesKobo = 0;
  if (unitPrice > 0 && event.pass_fees_to_buyer) {
    const perTicketFeeKobo = Math.round(unitPrice * 0.07 * 100) + 5000; // 7% + ₦50
    feesKobo = Math.max(0, perTicketFeeKobo * quantity);
  }
  
  // Coupon discount (simplified - assumes coupon is valid)
  let discountKobo = 0;
  let validCoupon = false;
  if (couponCode) {
    // In a full implementation, we would validate the coupon against the database
    // For now, we'll return the quote structure with coupon validation flag
    validCoupon = false; // Will be validated in the route handler
  }
  
  const totalKobo = Math.max(0, subtotalKobo - discountKobo + feesKobo);
  
  return {
    subtotal_kobo: subtotalKobo,
    fees_kobo: feesKobo,
    discount_kobo: discountKobo,
    total_kobo: totalKobo,
    coupon_valid: validCoupon,
    unit_price_kobo: Math.round(unitPrice * 100),
  };
}

async function validateCoupon(couponCode, eventId) {
  // Simplified coupon validation - in production, query the coupons table
  if (!couponCode) return null;
  
  // This would be a database query in production
  // For now, return null to indicate no valid coupon
  return null;
}

async function initializePaystackPayment(order, callbackUrl) {
  const secret = process.env.PAYSTACK_SECRET_KEY;
  if (!secret) {
    throw new Error('Paystack secret key not configured');
  }
  
  const response = await fetch('https://api.paystack.co/transaction/initialize', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${secret}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      email: order.buyer_email,
      amount: order.amount,
      reference: order.paystack_reference,
      callback_url: callbackUrl,
      currency: 'NGN',
    }),
  });
  
  const data = await response.json();
  
  if (!response.ok || !data.status) {
    throw new Error('Paystack initialization failed');
  }
  
  return data.data.authorization_url;
}

async function verifyPaystackTransaction(reference) {
  const secret = process.env.PAYSTACK_SECRET_KEY;
  if (!secret) {
    throw new Error('Paystack secret key not configured');
  }
  
  const response = await fetch(`https://api.paystack.co/transaction/verify/${reference}`, {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${secret}`,
    },
  });
  
  const data = await response.json();
  
  if (!response.ok) {
    throw new Error('Paystack verification failed');
  }
  
  return data.data;
}

async function finalizePayment(reference) {
  const order = await findOrderByReference(reference);
  if (!order) {
    return { success: false, error: 'Order not found' };
  }
  
  // Idempotency: if already paid, return success
  if (order.status === 'paid') {
    return { success: true, order };
  }
  
  try {
    const transaction = await verifyPaystackTransaction(reference);
    
    const status = transaction.status;
    const amount = transaction.amount;
    const currency = transaction.currency;
    
    if (status !== 'success' || amount !== order.amount || currency !== 'NGN') {
      await updateOrderStatusByReference(reference, 'failed');
      return { success: false, error: 'Payment verification failed' };
    }
    
    // Safely mark paid and reduce capacity without overselling
    await decrementEventCapacity(order.event_id, order.quantity);
    
    if (order.coupon_code) {
      await incrementCouponUses(order.coupon_code);
    }
    
    const updatedOrder = await updateOrderStatusByReference(reference, 'paid');
    return { success: true, order: updatedOrder };
  } catch (error) {
    await updateOrderStatusByReference(reference, 'failed');
    return { success: false, error: error.message };
  }
}

async function finalizeZeroCostOrder(order) {
  try {
    await decrementEventCapacity(order.event_id, order.quantity);
    
    if (order.coupon_code) {
      await incrementCouponUses(order.coupon_code);
    }
    
    const updatedOrder = await updateOrderStatus(order.id, 'paid');
    return { success: true, order: updatedOrder };
  } catch (error) {
    await updateOrderStatus(order.id, 'failed');
    return { success: false, error: error.message };
  }
}

module.exports = {
  generateReference,
  calculateQuote,
  validateCoupon,
  initializePaystackPayment,
  verifyPaystackTransaction,
  finalizePayment,
  finalizeZeroCostOrder,
};
