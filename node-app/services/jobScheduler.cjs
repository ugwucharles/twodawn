const { queueTicketEmail, processQueue } = require('./emailService.cjs');
const { createBackup } = require('./backupService.cjs');

const jobs = [];
let isRunning = false;

function scheduleJob(type, data, delay = 0) {
  const job = {
    id: Date.now() + Math.random().toString(36).substr(2, 9),
    type,
    data,
    scheduledAt: Date.now() + delay,
    status: 'pending',
  };
  
  jobs.push(job);
  jobs.sort((a, b) => a.scheduledAt - b.scheduledAt);
  
  processJobs();
  return job.id;
}

async function processJobs() {
  if (isRunning) return;
  
  const now = Date.now();
  const readyJobs = jobs.filter(j => j.scheduledAt <= now && j.status === 'pending');
  
  if (readyJobs.length === 0) return;
  
  isRunning = true;
  
  for (const job of readyJobs) {
    job.status = 'processing';
    
    try {
      switch (job.type) {
        case 'email':
          await queueTicketEmail(job.data.order, job.data.event);
          break;
        case 'backup':
          await createBackup(job.data.dbOnly);
          break;
        default:
          console.log(`Unknown job type: ${job.type}`);
      }
      
      job.status = 'completed';
    } catch (error) {
      console.error(`Job ${job.id} failed:`, error);
      job.status = 'failed';
      job.error = error.message;
    }
  }
  
  // Remove completed jobs older than 1 hour
  const oneHourAgo = Date.now() - 60 * 60 * 1000;
  const completedJobs = jobs.filter(j => j.status === 'completed' && j.scheduledAt < oneHourAgo);
  completedJobs.forEach(j => {
    const index = jobs.indexOf(j);
    if (index > -1) jobs.splice(index, 1);
  });
  
  isRunning = false;
  
  // Check for more jobs
  if (jobs.some(j => j.status === 'pending' && j.scheduledAt <= Date.now())) {
    processJobs();
  }
}

function getJobStatus(jobId) {
  return jobs.find(j => j.id === jobId) || null;
}

function getAllJobs() {
  return jobs;
}

// Start the job processor
setInterval(processJobs, 5000); // Check every 5 seconds

module.exports = {
  scheduleJob,
  processJobs,
  getJobStatus,
  getAllJobs,
};
