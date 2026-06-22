<?php
session_start();
include '../../includes/config.php';
include '../../includes/functions.php';

// Check if user is logged in and is admin
if(!is_logged_in() || !is_user_type('admin')) {
    redirect('../../login.php');
}

// Get system statistics
$stats = get_system_stats();

// Get monthly case data for the past 12 months
$monthly_case_data = [];
$labels = [];

for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    $labels[] = $month_name;
    
    // Get cases filed in this month
    $start_date = $month . '-01';
    $end_date = date('Y-m-t', strtotime($start_date));
    
    $sql = "SELECT COUNT(*) as count FROM cases WHERE filing_date BETWEEN '$start_date' AND '$end_date'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $monthly_case_data[] = $row['count'];
}

// Get case resolution time data
$sql = "SELECT 
            DATEDIFF(closing_date, filing_date) as resolution_days,
            YEAR(filing_date) as year,
            MONTH(filing_date) as month
        FROM cases 
        WHERE status = 'closed' AND closing_date IS NOT NULL
        ORDER BY filing_date";
$result = mysqli_query($conn, $sql);

$resolution_data = [];
$resolution_labels = [];

while ($row = mysqli_fetch_assoc($result)) {
    $month_year = date('M Y', strtotime($row['year'] . '-' . $row['month'] . '-01'));
    
    if (!isset($resolution_data[$month_year])) {
        $resolution_data[$month_year] = [
            'total' => 0,
            'count' => 0
        ];
    }
    
    $resolution_data[$month_year]['total'] += $row['resolution_days'];
    $resolution_data[$month_year]['count']++;
}

$avg_resolution_data = [];
foreach ($resolution_data as $month => $data) {
    $resolution_labels[] = $month;
    $avg_resolution_data[] = round($data['total'] / $data['count'], 1);
}

// Get case distribution by status
$case_status_data = [
    $stats['pending_cases'],
    $stats['in_progress_cases'],
    $stats['closed_cases']
];

// Page title
$page_title = "Reports & Analytics";
include '../includes/header.php';
?>

<!-- Main Content -->
<div class="flex-1 p-8 overflow-x-hidden overflow-y-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Reports & Analytics</h1>
        <p class="text-gray-600">Visualize system data and trends</p>
    </div>
    
    <!-- Filter Controls -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Filter Options</h2>
        <form method="get" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                <select name="date_range" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    <option value="last_12_months" selected>Last 12 Months</option>
                    <option value="last_6_months">Last 6 Months</option>
                    <option value="last_3_months">Last 3 Months</option>
                    <option value="this_year">This Year</option>
                    <option value="last_year">Last Year</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Case Type</label>
                <select name="case_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    <option value="all" selected>All Cases</option>
                    <option value="civil">Civil Cases</option>
                    <option value="criminal">Criminal Cases</option>
                    <option value="family">Family Cases</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>
    
    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Case Status Distribution -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Case Status Distribution</h2>
            <div class="h-80">
                <canvas id="caseStatusChart"></canvas>
            </div>
        </div>
        
        <!-- Monthly Case Filing Trend -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Monthly Case Filing Trend</h2>
            <div class="h-80">
                <canvas id="caseFilingChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Average Case Resolution Time -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Average Case Resolution Time (Days)</h2>
            <div class="h-80">
                <canvas id="resolutionTimeChart"></canvas>
            </div>
        </div>
        
        <!-- User Distribution -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">User Distribution</h2>
            <div class="h-80">
                <canvas id="userDistributionChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Export Options -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Export Reports</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="#" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <span>Export as PDF</span>
            </a>
            <a href="#" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                <div class="p-2 rounded-full bg-green-100 text-green-600 mr-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <span>Export as Excel</span>
            </a>
            <a href="#" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <span>Print Report</span>
            </a>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

<!-- Chart Initialization Scripts -->
<script>
    // Chart color palette
    const colors = {
        blue: 'rgba(59, 130, 246, 0.8)',
        blueLight: 'rgba(59, 130, 246, 0.2)',
        red: 'rgba(239, 68, 68, 0.8)',
        redLight: 'rgba(239, 68, 68, 0.2)',
        green: 'rgba(16, 185, 129, 0.8)',
        greenLight: 'rgba(16, 185, 129, 0.2)',
        yellow: 'rgba(245, 158, 11, 0.8)',
        yellowLight: 'rgba(245, 158, 11, 0.2)',
        purple: 'rgba(139, 92, 246, 0.8)',
        purpleLight: 'rgba(139, 92, 246, 0.2)',
    };

    // Case Status Distribution Chart
    const caseStatusCtx = document.getElementById('caseStatusChart').getContext('2d');
    const caseStatusChart = new Chart(caseStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'In Progress', 'Closed'],
            datasets: [{
                data: <?php echo json_encode($case_status_data); ?>,
                backgroundColor: [colors.yellow, colors.blue, colors.green],
                borderColor: ['#fff', '#fff', '#fff'],
                borderWidth: 2,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.raw;
                            const percentage = Math.round((value / total) * 100);
                            return `${context.label}: ${value} (${percentage}%)`;
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        }
    });

    // Monthly Case Filing Trend Chart
    const caseFilingCtx = document.getElementById('caseFilingChart').getContext('2d');
    const caseFilingChart = new Chart(caseFilingCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'New Cases Filed',
                data: <?php echo json_encode($monthly_case_data); ?>,
                backgroundColor: colors.blueLight,
                borderColor: colors.blue,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: colors.blue,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });

    // Average Case Resolution Time Chart
    const resolutionTimeCtx = document.getElementById('resolutionTimeChart').getContext('2d');
    const resolutionTimeChart = new Chart(resolutionTimeCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($resolution_labels); ?>,
            datasets: [{
                label: 'Average Days to Resolution',
                data: <?php echo json_encode($avg_resolution_data); ?>,
                backgroundColor: colors.greenLight,
                borderColor: colors.green,
                borderWidth: 2,
                borderRadius: 5,
                hoverBackgroundColor: colors.green
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Days'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            },
            animation: {
                delay: function(context) {
                    return context.dataIndex * 100;
                },
                duration: 1000
            }
        }
    });

    // User Distribution Chart
    const userDistributionCtx = document.getElementById('userDistributionChart').getContext('2d');
    const userDistributionChart = new Chart(userDistributionCtx, {
        type: 'polarArea',
        data: {
            labels: ['Admins', 'Clients', 'Judges'],
            datasets: [{
                data: [
                    <?php echo $stats['total_users'] - $stats['total_clients'] - $stats['total_judges']; ?>,
                    <?php echo $stats['total_clients']; ?>,
                    <?php echo $stats['total_judges']; ?>
                ],
                backgroundColor: [
                    colors.purple,
                    colors.blue,
                    colors.green
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
