// Based on jQuery.ganttView v.0.8.8 Copyright (c) 2010 JC Grubbs - jc.grubbs@devmynd.com - MIT License

KB.on('dom.ready', function () {
	let chartConfig;
	const GanttUtils = {
		formatTasks: (datas) => {
			let tasks = JSON.parse(datas);

			for (let i in tasks) {
				tasks[i].start = new Date(tasks[i].start[0], tasks[i].start[1] - 1, tasks[i].start[2]);
				tasks[i].end = new Date(tasks[i].end[0], tasks[i].end[1] - 1, tasks[i].end[2]);
				tasks[i].name = tasks[i].title;
				tasks[i].progress = parseInt(tasks[i].progress);
				if (tasks[i].progress <= 0) {
					tasks[i].progress = 1;
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
				url: config.saveUrl,
				contentType: 'application/json',
				type: 'POST',
				processData: false,
				data: JSON.stringify(record),
			});
		},
		//	onClick: function (task) {
		//		console.log(task);
		//		if (typeof task.onClickUrl != 'undefined') {
		//			console.log(task.onClickUrl);
		//		}
		//	},
		onDateChange: (task, start, end) => {
			task.start = start;
			task.end = end;
			GanttUtils.saveRecord(task, chartConfig);
		},
		onProgressChange: (task, progress) => {
			task.progress = progress;
			GanttUtils.saveRecord(task, chartConfig);
		},
		//onViewChange: (mode) => {},
	};

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
		chartConfig = config;

		new Promise(async (resolve) => {
			let tasks = await GanttUtils.formatTasks(config.records);
			return resolve(tasks);
		})
			.then((tasks) => {
				console.log(tasks);
				return new Gantt('#gantt-chart', tasks, {
					column_width: 30,
					step: 24,
					view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
					bar_height: 25,
					bar_corner_radius: 3,
					arrow_curve: 5,
					view_mode: 'Day',
					date_format: 'YYYY-MM-DD',
					popup_trigger: 'mouseover',
					//on_click: function (task) {
					//	return;
					//},
					on_date_change: function (task, start, end) {
						GanttUtils.onDateChange(task, start, end);
					},
					on_progress_change: function (task, progress) {
						GanttUtils.onProgressChange(task, progress);
					},
					//on_view_change: function (mode) {
					//	GanttUtils.onViewChange(mode);
					//},
				});
			})
			.then((chart) => {
				$('.btn-gantt-chart').on('click', function () {
					let $btn = $(this);
					var mode = $btn.text();

					chart.change_view_mode(mode);
					$btn.parent().parent().find('button').removeClass('active');
					$btn.addClass('active');
				});
			});
	}

	const getQueryParams = (params, url) => {
		let href = url;
		// this is an expression to get query strings
		let regexp = new RegExp('[?&]' + params + '=([^&#]*)', 'i');
		let qString = regexp.exec(href);
		return qString ? qString[1] : null;
	};

	// Fix search filter error -> missing plugin name
	let plugin = document.getElementById('form-plugin');
	if (plugin !== null) {
		plugin.value = getQueryParams('plugin', window.location.href);
	}
});
