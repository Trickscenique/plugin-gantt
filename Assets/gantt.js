// Based on jQuery.ganttView v.0.8.8 Copyright (c) 2010 JC Grubbs - jc.grubbs@devmynd.com - MIT License
let chartConfig;
const GanttUtils = {
	formatTasks: (datas) => {
		let tasks = JSON.parse(datas);

		for (let i = 0; i < tasks.length; i++) {
			let start = new Date(tasks[i].start[0], tasks[i].start[1] - 1, tasks[i].start[2], 0, 0, 0, 0);
			tasks[i].start = start;

			let end = new Date(tasks[i].end[0], tasks[i].end[1] - 1, tasks[i].end[2], 0, 0, 0, 0);
			tasks[i].end = end;
			tasks[i].name = tasks[i].title;
			tasks[i].progress = parseInt(tasks[i].progress);
			if (tasks[i].progress < 0) {
				tasks[i].progress = 0;
			}
			tasks[i].custom_class = 'color-' + tasks[i].color.name.toLowerCase();
		}

		return tasks;
	},
	saveRecord: (record, config) => {
		record['start_date'] = record.start.toString();
		record['end_date'] = record.end.toString();
		$.ajax({
			cache: false,
			url: config['save-url'],
			contentType: 'application/json',
			type: 'POST',
			processData: false,
			data: JSON.stringify(record),
		});
	},
	onClick: (task) => {},
	onDateChange: (task, start, end) => {
		task.start = start;
		task.end = end;
		console.log(task);
		GanttUtils.saveRecord(task, chartConfig);
	},
	onProgressChange: (task, progress) => {
		task.progress = progress;
		console.log(task);
		GanttUtils.saveRecord(task, chartConfig);
	},
	onViewChange: (mode) => {},
};

KB.on('dom.ready', function () {
	function goToLink(selector) {
		if (!KB.modal.isOpen()) {
			let element = KB.find(selector);

			if (element !== null) {
				window.location = element.attr('href');
			}
		}
	}

	KB.onKey('v+g', function () {
		goToLink('a.view-gantt');
	});

	if (KB.exists('#gantt-chart')) {
		let container = document.getElementById('gantt-chart');
		let config = container.dataset;
		console.log(config);
		chartConfig = config;
		let chart = new Gantt('#gantt-chart', GanttUtils.formatTasks(config.records), {
			column_width: 30,
			step: 24,
			view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
			bar_height: 20,
			bar_corner_radius: 3,
			arrow_curve: 5,
			view_mode: 'Day',
			date_format: 'YYYY-MM-DD',
			custom_popup_html: function (task) {
				// the task object will contain the updated
				// dates and progress value
				const end_date = task.end.toLocaleDateString();
				return `
                  <div class="details-container">
                    <h5>${task.name}</h5>
                    <p>Echéance le ${end_date}</p>
                    <p>${task.progress}% complété!</p>
                  </div>
                `;
			},
			on_click: function (task) {
				GanttUtils.onClick(task);
			},
			on_date_change: function (task, start, end) {
				GanttUtils.onDateChange(task, start, end);
			},
			on_progress_change: function (task, progress) {
				GanttUtils.onProgressChange(task, progress);
			},
			on_view_change: function (mode) {
				GanttUtils.onViewChange(mode);
			},
		});
	}
});
