<?php

namespace Kanboard\Plugin\Gantt\Controller;

use DateTime;
use Kanboard\Controller\BaseController;
use Kanboard\Filter\TaskProjectFilter;
use Kanboard\Model\TaskModel;

/**
 * Tasks Gantt Controller
 *
 * @package  Kanboard\Controller
 * @author   Frederic Guillot
 * @property \Kanboard\Plugin\Gantt\Formatter\TaskGanttFormatter $taskGanttFormatter
 */
class TaskGanttController extends BaseController
{
    /**
     * Show Gantt chart for one project
     */
    public function show()
    {
        $project = $this->getProject();
        if (isset($_GET['search'])) {
            $search = $this->helper->projectHeader->getSearchQuery($project);
        } else {
            $search = 'n';
        }

        $sorting = $this->request->getStringParam('sorting', '');
        $filter = $this->taskLexer->build($search)->withFilter(new TaskProjectFilter($project['id']));

        if ($sorting === '') {
            $sorting = $this->configModel->get('gantt_task_sort', 'board');
        }

        if ($sorting === 'date') {
            $filter->getQuery()->desc(TaskModel::TABLE.'.date_started')->addCondition(TaskModel::TABLE.'.date_started >= ' .(new DateTime())->format("u"));
            $tasks = $filter->format($this->taskGanttFormatter);
            $filter->getQuery()->desc(TaskModel::TABLE.'.date_started')->addCondition(TaskModel::TABLE.'.date_started < ' .(new DateTime())->format("u"));
            $tasks = array_merge($tasks, $filter->format($this->taskGanttFormatter));
        } else {
            $filter->getQuery()->asc('column_position')->asc(TaskModel::TABLE.'.position');
            $tasks = $filter->format($this->taskGanttFormatter);
        }


        $this->response->html($this->helper->layout->app('Gantt:task_gantt/show', array(
            'project' => $project,
            'title' => $project['name'],
            'description' => $this->helper->projectHeader->getDescription($project),
            'sorting' => $sorting,
            'tasks' => $tasks,
        )));
    }


    /**
     * Save new task start date and due date
     */
    public function save()
    {
        $this->getProject();
        $changes = $this->request->getJson();
        $values = [];

        if (! empty($changes['start'])) {
            $values['date_started'] = strtotime($changes['start']);
        }

        if (! empty($changes['end'])) {
            $values['date_due'] = strtotime($changes['end']);
        }

        if (! empty($values)) {
            $elements = explode("-", $changes['id']);
            $values['id'] = $elements[1];
            if ($elements[0] === "task") {
                $result = $this->taskModificationModel->update($values);
            } else {
                unset($values['date_started']);
                $values['due_date'] = $values['date_due'];
                unset($values['date_due']);
                $result = $this->subtaskModel->update($values);
            }

            if (! $result) {
                $this->response->json(array('message' => 'Unable to save task'), 400);
            } else {
                $this->response->json(array('message' => 'OK'), 201);
            }
        } else {
            $this->response->json(array('message' => 'Ignored'), 200);
        }
    }
}
