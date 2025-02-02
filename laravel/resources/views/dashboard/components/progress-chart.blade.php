@props(['data'])

@php
    $chartId = 'progressChart_' . uniqid();
@endphp

<div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="p-5">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
            タスク進捗状況
        </h3>
        <div class="relative" style="height: 300px;">
            <canvas id="{{ $chartId }}"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('{{ $chartId }}').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: @json($data['labels']),
            datasets: [{
                data: @json($data['data']),
                backgroundColor: @json($data['backgroundColor']),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    padding: 12,
                    bodyFont: {
                        size: 13
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            }
        }
    });
});
</script>
@endpush 