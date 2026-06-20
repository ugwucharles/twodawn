const { query } = require('../db/client.cjs');

async function exportOrdersCSV(filters = {}) {
  const { event_id, from, to } = filters;
  
  let whereClause = 'WHERE 1=1';
  const params = [];
  
  if (event_id) {
    whereClause += ' AND o.event_id = ?';
    params.push(event_id);
  }
  
  if (from) {
    whereClause += ' AND o.created_at >= ?';
    params.push(from);
  }
  
  if (to) {
    whereClause += ' AND o.created_at <= ?';
    params.push(to);
  }
  
  const rows = await query(`
    SELECT 
      o.created_at,
      e.title as event_title,
      o.buyer_name,
      o.buyer_email,
      o.buyer_phone,
      o.quantity,
      o.amount,
      o.status,
      o.paystack_reference
    FROM orders o
    LEFT JOIN events e ON o.event_id = e.id
    ${whereClause}
    ORDER BY o.created_at DESC
  `, params);
  
  const headers = ['Date', 'Event', 'Buyer Name', 'Buyer Email', 'Buyer Phone', 'Qty', 'Amount (NGN)', 'Status', 'Reference'];
  const csvRows = [headers.join(',')];
  
  for (const row of rows) {
    const date = row.created_at ? new Date(row.created_at).toISOString().slice(0, 16).replace('T', ' ') : '';
    const amount = row.amount ? (Number(row.amount) / 100).toFixed(2) : '0.00';
    
    csvRows.push([
      date,
      row.event_title || '',
      row.buyer_name || '',
      row.buyer_email || '',
      row.buyer_phone || '',
      row.quantity || 0,
      amount,
      row.status || '',
      row.paystack_reference || '',
    ].join(','));
  }
  
  return csvRows.join('\n');
}

async function exportSalesSummaryCSV(filters = {}) {
  const { event_id, from, to } = filters;
  
  let whereClause = 'WHERE o.status = "paid"';
  const params = [];
  
  if (event_id) {
    whereClause += ' AND o.event_id = ?';
    params.push(event_id);
  }
  
  if (from) {
    whereClause += ' AND o.created_at >= ?';
    params.push(from);
  }
  
  if (to) {
    whereClause += ' AND o.created_at <= ?';
    params.push(to);
  }
  
  const rows = await query(`
    SELECT 
      o.event_id,
      e.title as event_title,
      COUNT(*) as orders,
      SUM(o.quantity) as tickets,
      SUM(o.amount) as gross
    FROM orders o
    LEFT JOIN events e ON o.event_id = e.id
    ${whereClause}
    GROUP BY o.event_id, e.title
  `, params);
  
  const headers = ['Event ID', 'Event Title', 'Paid Orders', 'Tickets Sold', 'Gross (NGN)', 'Avg Price (NGN)'];
  const csvRows = [headers.join(',')];
  
  for (const row of rows) {
    const tickets = Number(row.tickets || 0);
    const gross = Number(row.gross || 0);
    const avg = tickets > 0 ? (gross / 100 / tickets).toFixed(2) : '0.00';
    
    csvRows.push([
      row.event_id || '',
      row.event_title || '',
      row.orders || 0,
      tickets,
      (gross / 100).toFixed(2),
      avg,
    ].join(','));
  }
  
  return csvRows.join('\n');
}

async function exportSalesDailyCSV(filters = {}) {
  const { event_id, from, to } = filters;
  
  let whereClause = 'WHERE o.status = "paid"';
  const params = [];
  
  if (event_id) {
    whereClause += ' AND o.event_id = ?';
    params.push(event_id);
  }
  
  if (from) {
    whereClause += ' AND o.created_at >= ?';
    params.push(from);
  }
  
  if (to) {
    whereClause += ' AND o.created_at <= ?';
    params.push(to);
  }
  
  const rows = await query(`
    SELECT 
      o.event_id,
      DATE(o.created_at) as day,
      e.title as event_title,
      COUNT(*) as orders,
      SUM(o.quantity) as tickets,
      SUM(o.amount) as gross
    FROM orders o
    LEFT JOIN events e ON o.event_id = e.id
    ${whereClause}
    GROUP BY o.event_id, DATE(o.created_at), e.title
    ORDER BY day
  `, params);
  
  const headers = ['Date', 'Event ID', 'Event Title', 'Paid Orders', 'Tickets Sold', 'Gross (NGN)', 'Avg Price (NGN)'];
  const csvRows = [headers.join(',')];
  
  for (const row of rows) {
    const tickets = Number(row.tickets || 0);
    const gross = Number(row.gross || 0);
    const avg = tickets > 0 ? (gross / 100 / tickets).toFixed(2) : '0.00';
    
    csvRows.push([
      row.day || '',
      row.event_id || '',
      row.event_title || '',
      row.orders || 0,
      tickets,
      (gross / 100).toFixed(2),
      avg,
    ].join(','));
  }
  
  return csvRows.join('\n');
}

module.exports = {
  exportOrdersCSV,
  exportSalesSummaryCSV,
  exportSalesDailyCSV,
};
