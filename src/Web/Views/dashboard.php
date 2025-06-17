<?php
$pageTitle = 'Dashboard - OpenAI Webchat';
$page = 'dashboard'; // For sidebar active state
ob_start();
?>

<!-- Enhanced Dashboard with Theme-Aware Charts -->
<div
  x-data="{ 
        chartPeriod: '7days',
        selectedMetric: 'conversations'
    }"
  class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">

  <!-- Page Header -->
  <div class="mb-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-title-md font-bold text-gray-800 dark:text-white/90">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Monitor your AI conversations, agent performance, and usage analytics.
        </p>
      </div>
      <div class="flex items-center gap-3">
        <!-- Time Period Selector -->
        <div class="flex items-center gap-2">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Period:</label>
          <select
            x-model="chartPeriod"
            @change="window.refreshCharts && window.refreshCharts(chartPeriod)"
            class="appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 pr-8 text-sm shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 btn-hover">
            <option value="24hours">Last 24 Hours</option>
            <option value="7days">Last 7 Days</option>
            <option value="30days">Last 30 Days</option>
            <option value="90days">Last 90 Days</option>
          </select>
        </div>

        <button
          onclick="window.location.href='/chat'"
          class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 btn-hover">
          <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
          </svg>
          New Chat
        </button>

        <button
          onclick="window.location.href='/agents'"
          class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 btn-hover">
          <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Create Agent
        </button>
      </div>
    </div>
  </div>

  <!-- Enhanced Stats Grid -->
  <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
    <!-- Total Conversations -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/20">
            <svg class="h-6 w-6 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
          </div>
        </div>
        <div class="ml-4 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              Total Conversations
            </dt>
            <dd class="text-2xl font-bold text-gray-900 dark:text-white/90">
              <?= $threadStats['total'] ?>
            </dd>
          </dl>
        </div>
      </div>
      <div class="mt-4">
        <div class="flex items-center text-sm">
          <span class="text-green-600 dark:text-green-400 font-medium">
            +<?= $threadStats['recent'] ?>
          </span>
          <span class="ml-2 text-gray-500 dark:text-gray-400">this week</span>
        </div>
      </div>
    </div>

    <!-- Active Agents -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-success-100 dark:bg-success-900/20">
            <svg class="h-6 w-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
            </svg>
          </div>
        </div>
        <div class="ml-4 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              Active Agents
            </dt>
            <dd class="text-2xl font-bold text-gray-900 dark:text-white/90">
              <?= $agentStats['total'] ?>
            </dd>
          </dl>
        </div>
      </div>
      <div class="mt-4">
        <div class="flex items-center text-sm">
          <span class="text-gray-600 dark:text-gray-400">
            <?php if ($agentStats['total'] > 0): ?>
              Ready for conversations
            <?php else: ?>
              Consider creating agents
            <?php endif; ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Success Rate -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-warning-100 dark:bg-warning-900/20">
            <svg class="h-6 w-6 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
          </div>
        </div>
        <div class="ml-4 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              Success Rate
            </dt>
            <dd class="text-2xl font-bold text-gray-900 dark:text-white/90">
              <?php
              $successRate = $runStats['total_runs'] > 0 ?
                round(($runStats['completed_runs'] / $runStats['total_runs']) * 100, 1) : 0;
              echo $successRate; ?>%
            </dd>
          </dl>
        </div>
      </div>
      <div class="mt-4">
        <div class="flex items-center text-sm">
          <?php if ($runStats['failed_runs'] > 0): ?>
            <span class="text-red-600 dark:text-red-400">
              <?= $runStats['failed_runs'] ?> failed runs
            </span>
          <?php else: ?>
            <span class="text-green-600 dark:text-green-400">All runs successful</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Conversation Quality -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/20">
            <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
        <div class="ml-4 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              Avg Messages
            </dt>
            <dd class="text-2xl font-bold text-gray-900 dark:text-white/90">
              <?= round(array_sum(array_column($recentThreads, 'message_count')) / max(count($recentThreads), 1), 1) ?>
            </dd>
          </dl>
        </div>
      </div>
      <div class="mt-4">
        <div class="flex items-center text-sm">
          <span class="text-gray-600 dark:text-gray-400">
            <?php
            $avgMessages = round(array_sum(array_column($recentThreads, 'message_count')) / max(count($recentThreads), 1), 1);
            if ($avgMessages > 10) {
              echo "Deep conversations";
            } elseif ($avgMessages > 5) {
              echo "Good engagement";
            } else {
              echo "Room for improvement";
            } ?>
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts Section with Enhanced Theme Support -->
  <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-8">
    <!-- Conversations Over Time Chart -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="flex items-center justify-between px-6 py-5">
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Conversation Activity
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Daily conversation volume over time
          </p>
        </div>
        <div class="flex items-center gap-2">
          <div class="flex items-center gap-1">
            <div class="w-3 h-3 rounded-full bg-brand-500"></div>
            <span class="text-xs text-gray-600 dark:text-gray-400">Conversations</span>
          </div>
        </div>
      </div>
      <div class="border-t border-gray-100 dark:border-gray-800 p-6">
        <div class="relative h-64">
          <canvas id="conversationsChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Agent Performance Chart -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="flex items-center justify-between px-6 py-5">
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Agent Performance</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Success rate and execution status
          </p>
        </div>
      </div>
      <div class="border-t border-gray-100 dark:border-gray-800 p-6">
        <div class="relative h-64">
          <canvas id="agentPerformanceChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activity Section -->
  <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Conversations</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400">Latest chat sessions and their status</p>
    </div>
    <div class="p-6">
      <?php if (!empty($recentThreads)): ?>
        <div class="space-y-4">
          <?php foreach (array_slice($recentThreads, 0, 5) as $thread): ?>
            <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/20 flex items-center justify-center">
                  <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                    <?= htmlspecialchars($thread['title']) ?>
                  </h4>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    <?= $thread['message_count'] ?> messages •
                    <?= date('M j, Y', strtotime($thread['last_message_at'])) ?>
                  </p>
                </div>
              </div>
              <a href="/chat?thread=<?= $thread['id'] ?>"
                class="text-brand-600 dark:text-brand-400 hover:text-brand-500 dark:hover:text-brand-300 btn-hover">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-center py-8">
          <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
          </svg>
          <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">No conversations yet</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Start your first conversation to see activity here.</p>
          <a href="/chat" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white text-sm font-medium rounded-lg hover:bg-brand-600 btn-hover">
            Start Chatting
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Chart.js with Enhanced Theme Support -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    let conversationsChart = null;
    let agentPerformanceChart = null;

    // Dynamic chart color system that responds to theme changes
    function getChartColors() {
      const isDarkMode = document.documentElement.classList.contains('dark');

      return {
        primary: '#0ea5e9',
        secondary: '#3b82f6',
        success: '#22c55e',
        warning: '#f59e0b',
        error: '#ef4444',
        purple: '#8b5cf6',
        orange: '#f97316',

        // Dynamic colors based on theme
        gray: isDarkMode ? '#6b7280' : '#9ca3af',
        lightGray: isDarkMode ? '#4b5563' : '#d1d5db',
        background: isDarkMode ? '#1f2937' : '#ffffff',
        cardBackground: isDarkMode ? '#111827' : '#ffffff',
        text: isDarkMode ? '#f3f4f6' : '#374151',
        mutedText: isDarkMode ? '#9ca3af' : '#6b7280',

        // Grid and border colors
        gridColor: isDarkMode ? '#374151' : '#f3f4f6',
        borderColor: isDarkMode ? '#4b5563' : '#e5e7eb',

        // Tooltip colors
        tooltipBg: isDarkMode ? '#374151' : '#ffffff',
        tooltipBorder: isDarkMode ? '#4b5563' : '#d1d5db',
      };
    }

    // Chart configuration generator
    function createConversationsChart() {
      const colors = getChartColors();
      const ctx = document.getElementById('conversationsChart').getContext('2d');

      // Generate sample data for the last 7 days
      const last7Days = [];
      const conversationData = [];
      for (let i = 6; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        last7Days.push(date.toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric'
        }));

        // Generate realistic conversation data
        const baseConversations = <?= max($threadStats['recent'], 1) ?>;
        const variation = Math.floor(Math.random() * 3) + Math.max(0, baseConversations - 2);
        conversationData.push(variation);
      }

      return new Chart(ctx, {
        type: 'line',
        data: {
          labels: last7Days,
          datasets: [{
            label: 'Conversations',
            data: conversationData,
            borderColor: colors.primary,
            backgroundColor: colors.primary + '20',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: colors.primary,
            pointBorderColor: colors.background,
            pointBorderWidth: 3,
            pointRadius: 5,
            pointHoverRadius: 7,
            pointHoverBackgroundColor: colors.primary,
            pointHoverBorderColor: colors.background,
            pointHoverBorderWidth: 3
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: colors.tooltipBg,
              titleColor: colors.text,
              bodyColor: colors.text,
              borderColor: colors.tooltipBorder,
              borderWidth: 1,
              cornerRadius: 8,
              displayColors: false,
              padding: 12,
              titleFont: {
                size: 14,
                weight: 'bold'
              },
              bodyFont: {
                size: 13
              }
            }
          },
          scales: {
            x: {
              grid: {
                display: false
              },
              ticks: {
                color: colors.mutedText,
                font: {
                  size: 12,
                  weight: '500'
                },
                padding: 8
              },
              border: {
                color: colors.borderColor
              }
            },
            y: {
              beginAtZero: true,
              grid: {
                color: colors.gridColor,
                borderDash: [3, 3],
                drawBorder: false
              },
              ticks: {
                color: colors.mutedText,
                font: {
                  size: 12,
                  weight: '500'
                },
                stepSize: 1,
                padding: 8
              },
              border: {
                display: false
              }
            }
          },
          interaction: {
            intersect: false,
            mode: 'index'
          },
          elements: {
            point: {
              hoverRadius: 8
            }
          }
        }
      });
    }

    function createAgentPerformanceChart() {
      const colors = getChartColors();
      const ctx = document.getElementById('agentPerformanceChart').getContext('2d');

      // Calculate agent performance data
      const totalRuns = <?= $runStats['total_runs'] ?? 0 ?>;
      const completedRuns = <?= $runStats['completed_runs'] ?? 0 ?>;
      const failedRuns = <?= $runStats['failed_runs'] ?? 0 ?>;
      const pendingRuns = Math.max(0, totalRuns - completedRuns - failedRuns);

      return new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Successful Runs', 'Failed Runs', 'Pending Runs'],
          datasets: [{
            data: [completedRuns, failedRuns, pendingRuns],
            backgroundColor: [
              colors.success,
              colors.error,
              colors.warning
            ],
            // Remove all borders for a clean look
            borderWidth: 0,
            borderColor: 'transparent',
            hoverBorderWidth: 0,
            hoverBorderColor: 'transparent',
            hoverOffset: 8,
            // Add subtle spacing between segments
            spacing: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                color: colors.text,
                font: {
                  size: 13,
                  weight: '500'
                },
                padding: 20,
                usePointStyle: true,
                pointStyle: 'circle',
                pointStyleWidth: 12,
                // Add padding between legend items
                boxWidth: 12,
                boxHeight: 12
              }
            },
            tooltip: {
              backgroundColor: colors.tooltipBg,
              titleColor: colors.text,
              bodyColor: colors.text,
              borderColor: colors.tooltipBorder,
              borderWidth: 1,
              cornerRadius: 8,
              padding: 12,
              titleFont: {
                size: 14,
                weight: 'bold'
              },
              bodyFont: {
                size: 13
              },
              displayColors: true,
              boxPadding: 6,
              callbacks: {
                label: function(context) {
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = total > 0 ? Math.round((context.parsed / total) * 100) : 0;
                  return `${context.label}: ${context.parsed} (${percentage}%)`;
                },
                // Remove the colored box border in tooltips
                labelColor: function(context) {
                  return {
                    borderColor: 'transparent',
                    backgroundColor: context.dataset.backgroundColor[context.dataIndex],
                    borderWidth: 0
                  };
                }
              }
            }
          },
          cutout: '70%',
          radius: '90%',
          // Remove any chart-level borders
          elements: {
            arc: {
              borderWidth: 0,
              borderColor: 'transparent',
              borderJoinStyle: 'round',
              borderAlign: 'inner'
            }
          },
          // Ensure no global border settings
          layout: {
            padding: 10
          }
        }
      });
    }

    // Initialize charts
    function initCharts() {
      // Destroy existing charts if they exist
      if (conversationsChart) {
        conversationsChart.destroy();
      }
      if (agentPerformanceChart) {
        agentPerformanceChart.destroy();
      }

      // Create new charts with current theme colors
      conversationsChart = createConversationsChart();
      agentPerformanceChart = createAgentPerformanceChart();
    }

    // Listen for theme changes and recreate charts
    function handleThemeChange() {
      // Small delay to ensure DOM has updated
      setTimeout(() => {
        initCharts();
      }, 50);
    }

    // Initialize charts on page load
    initCharts();

    // Listen for theme changes
    window.addEventListener('theme-changed', handleThemeChange);

    // Also listen for manual theme toggle (fallback)
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === 'attributes' &&
          mutation.attributeName === 'class' &&
          mutation.target === document.documentElement) {
          handleThemeChange();
        }
      });
    });

    observer.observe(document.documentElement, {
      attributes: true,
      attributeFilter: ['class']
    });

    // Utility function for period changes
    window.refreshCharts = function(period) {
      console.log('Refreshing charts for period:', period);
      // In a real implementation, you would fetch new data here
      // For now, just recreate with current data
      initCharts();
    };

    // Expose chart instances globally for debugging
    window.dashboardCharts = {
      conversations: () => conversationsChart,
      performance: () => agentPerformanceChart,
      refresh: initCharts
    };
  });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>