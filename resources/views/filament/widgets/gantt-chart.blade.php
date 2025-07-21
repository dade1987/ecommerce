<div id="gantt-container" wire:ignore>
    <svg id="gantt"></svg>
</div>

@assets
<script src="{{ asset('node_modules/frappe-gantt/dist/frappe-gantt.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('node_modules/frappe-gantt/dist/frappe-gantt.css') }}">
@endassets

@script
<script>
    let ganttChart;

    function initializeGantt() {
        fetch('/api/gantt-data', {
            headers: {
                'Authorization': 'Bearer ' + '{{-- Your Sanctum API token here --}}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(tasks => {
            if (tasks.length > 0) {
                // Clear the container before creating a new chart
                const container = document.getElementById('gantt-container');
                container.innerHTML = '<svg id="gantt"></svg>';
                
                ganttChart = new Gantt("#gantt", tasks, {
                    header_height: 50,
                    column_width: 30,
                    step: 24,
                    view_modes: ['Day', 'Week', 'Month'],
                    bar_height: 20,
                    padding: 18,
                    view_mode: 'Week',
                    language: 'it'
                });
            }
        });
    }

    document.addEventListener('livewire:load', function () {
        initializeGantt();
        
        window.addEventListener('gantt-updated', event => {
            console.log('gantt-updated event received');
            initializeGantt();
        });
    });
</script>
@endscript 