<section id="main">
    <?= $this->projectHeader->render($project, 'TaskGanttController', 'show', false, 'Gantt') ?>
    <div class="menu-inline">
        <ul>
            <li <?= $sorting === 'board' ? 'class="active"' : '' ?>>
                <?= $this->url->icon('sort-numeric-asc', t('Sort by position'), 'TaskGanttController', 'show', array('project_id' => $project['id'], 'sorting' => 'board', 'plugin' => 'Gantt')) ?>
            </li>
            <li <?= $sorting === 'date' ? 'class="active"' : '' ?>>
                <?= $this->url->icon('sort-amount-asc', t('Sort by date'), 'TaskGanttController', 'show', array('project_id' => $project['id'], 'sorting' => 'date', 'plugin' => 'Gantt')) ?>
            </li>
            <li>
                <?= $this->modal->large('plus', t('Add task'), 'TaskCreationController', 'show', array('project_id' => $project['id'])) ?>
            </li>
            <li>
                <button type="button" class="btn dropdown-menu dropdown-menu-link-icon btn-gantt-chart">Quarter Day</button>
            </li>
            <li>
                <button type="button" class="btn dropdown-menu dropdown-menu-link-icon btn-gantt-chart">Half Day</button>
            </li>
            <li>
                <button type="button" class="btn dropdown-menu dropdown-menu-link-icon btn-gantt-chart active">Day</button>
            </li>
            <li>
                <button type="button" class="btn dropdown-menu dropdown-menu-link-icon btn-gantt-chart">Week</button>
            </li>
            <li>
                <button type="button" class="btn dropdown-menu dropdown-menu-link-icon btn-gantt-chart">Month</button>
            </li>

        </ul>
    </div>

    <?php if (! empty($tasks)): ?>

        <?php foreach ($tasks as $task): ?>
            <?php $elements = explode("-", $task['id']);
            $task['id'] = $elements[1];
            ?>
            <div id="dropdown-task-id-<?= implode('-', $elements) ?>" style="display: none;">
            <?php if ($elements[0] == "task"): ?>
                  <?= $this->render('task/dropdown', array('task' => $task, 'redirect' => 'board')) ?>
            <?php else: ?>
                <?= $this->render('subtask/menu', array('task' => $task['task'] ?? [], 'subtask' => $task)) ?>
            <?php endif ?>
            </div>
        <?php endforeach ?>
        <svg
            id="gantt-chart"
            data-records='<?= json_encode($tasks, JSON_HEX_APOS) ?>'
            data-save-url="<?= $this->url->href('TaskGanttController', 'save', array('project_id' => $project['id'], 'plugin' => 'Gantt')) ?>"
            data-label-start-date="<?= t('Start date:') ?>"
            data-label-end-date="<?= t('Due date:') ?>"
            data-label-assignee="<?= t('Assignee:') ?>"
            data-label-not-defined="<?= t('There is no start date or due date for this task.') ?>"
        ></svg>
        <p class="alert alert-info"><?= t('Moving or resizing a task will change the start and due date of the task.') ?></p>
    <?php else: ?>
        <p class="alert"><?= t('There is no task in your project.') ?></p>
    <?php endif ?>
</section>
