<?php

namespace Kanboard\Plugin\Gantt\Formatter;

use Kanboard\Core\Filter\FormatterInterface;
use Kanboard\Formatter\BaseFormatter;

/**
 * Task Gantt Formatter
 *
 * @package formatter
 * @author  Frederic Guillot
 */
class TaskGanttFormatter extends BaseFormatter implements FormatterInterface
{
    /**
     * Local cache for project columns
     *
     * @access private
     * @var array
     */
    private $columns = array();

    private $links = [];

    /**
     * Apply formatter
     *
     * @access public
     * @return array
     */
    public function format()
    {
        $bars = array();

        foreach ($this->query->findAll() as $task) {
            $bars[] = $this->formatTask($task);
        }

        return $bars;
    }

    /**
     * Format a single task
     *
     * @access private
     * @param  array  $task
     * @return array
     */
    private function formatTask(array $task)
    {
        if (! isset($this->columns[$task['project_id']])) {
            $this->columns[$task['project_id']] = $this->columnModel->getList($task['project_id']);
        }

        $start = $task['date_started'] ?: time();
        $end = $task['date_due'] ?: $start;

        $array =  array(
            'type' => 'task',
            'id' => $task['id'],
            'title' => $task['title'],
            'start' => array(
                (int) date('Y', $start),
                (int) date('n', $start),
                (int) date('j', $start),
            ),
            'end' => array(
                (int) date('Y', $end),
                (int) date('n', $end),
                (int) date('j', $end),
            ),
            'dependencies' =>  implode(",", $this->getLinksId($task['id'])),
            'column_title' => $task['column_name'],
            'assignee' => $task['assignee_name'] ?: $task['assignee_username'],
            'progress' => $this->taskModel->getProgress($task, $this->columns[$task['project_id']]),
            'link' => $this->helper->url->href('TaskViewController', 'show', array('project_id' => $task['project_id'], 'task_id' => $task['id'])),
            'color' => $this->colorModel->getColorProperties($task['color_id']),
            'not_defined' => empty($task['date_due']) || empty($task['date_started']),
            'date_started_not_defined' => empty($task['date_started']),
            'date_due_not_defined' => empty($task['date_due']),
        );

        if ($this->helper->projectRole->canUpdateTask($task)) {
            $array['onClickUrl'] = $this->helper->url->href('TaskModificationController', 'edit', array('project_id' => $task['project_id'], 'task_id' => $task['id']));
        }
        return $array;
    }

    /**
     * @todo must be in TaskLinkModel
     *
     * @param integer $id
     *
     * @return array
     */
    private function getLinksId(int $id) :array
    {
        $links = $this->taskLinkModel->getAll($id);

        $result = [];

        foreach ($links as $link) {
            $uiid = $link['task_id'].':'.$id;
            $uiid2 = $id.":".$link['task_id'];
            $label = $link['label'] ?? '';
            if ($link['task_id'] != $id && !in_array($uiid, $this->links) && !in_array($uiid2, $this->links) && !str_contains($label, "is ")) {
                $result[] = $link['task_id'];
                $this->links[] = $uiid2;
                $this->links[] = $uiid;
            }
        }

        return $result;
    }
}
