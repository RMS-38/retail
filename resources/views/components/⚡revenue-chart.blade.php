
<?php

use Livewire\Component;

new class extends Component {
    public array $revenue = [
        25, 30, 28, 40, 37, 45, 42, 50
    ];
};
?>

<flux:card>
    <div class="mb-4">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-white">
            Revenue overview
        </h3>

        <p class="mt-1 text-xs text-slate-500 dark:text-white">
            Monthly revenue · 2026
        </p>
    </div>

    <div wire:ignore class="h-32">
        <canvas></canvas>
    </div>
</flux:card>

<script>
    const canvas = $wire.$el.querySelector('canvas');

      // Détection du thème actuel
    const isDark = () =>
        document.documentElement.classList.contains('dark');

    const getColors = () => {
        const dark = isDark();

        return {
            line: dark ? '#60A5FA' : '#4F7DF3',
            ticks: dark ? '#A1A1AA' : '#64748B',
            grid: dark ? '#3F3F46' : '#EDF1F7'
        };
    };

    const colors = getColors();

    const chart = new Chart(canvas, {
        type: 'line',

        data: {
            labels: [
                'Jan', 'Feb', 'Mar', 'Apr',
                'May', 'Jun', 'Jul', 'Aug'
            ],

            datasets: [{
                data: $wire.revenue,
                borderColor: colors.line,
                backgroundColor: colors.line,
                borderWidth: 2.5,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: colors.line,
                pointBorderWidth: 0,
                tension: 0.15
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    display: false
                }
            },

            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    border: {
                        display: false
                    },
                    ticks: {
                        color: colors.ticks,
                        font: { size: 9 }
                    }
                },

                y: {
                    min: 0,
                    ticks: {
                        display: false
                    },
                    grid: {
                        color: colors.grid,
                        drawTicks: false
                    },
                    border: {
                        display: false
                    }
                }
            }
        }
    });

    $wire.$watch('revenue', (value) => {
        chart.data.datasets[0].data = value;
        chart.update();
    });

     // Observer : changement Dark / Light
    const observer = new MutationObserver(() => {
        const colors = getColors();

        chart.data.datasets[0].borderColor = colors.line;
        chart.data.datasets[0].backgroundColor = colors.line;
        chart.data.datasets[0].pointBackgroundColor = colors.line;

        chart.options.scales.x.ticks.color = colors.ticks;
        chart.options.scales.y.grid.color = colors.grid;

        chart.update();
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });

    // Mise à jour des données Livewire
    $wire.$watch('revenue', (value) => {
        chart.data.datasets[0].data = value;
        chart.update();
    });
</script>
