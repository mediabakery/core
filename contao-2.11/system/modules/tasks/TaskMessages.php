<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Leo Feyer 2005-2011
 * @author     Leo Feyer <http://www.contao.org>
 * @package    TaskCenter
 * @license    LGPL
 * @filesource
 */


/**
 * Class TaskMessages
 *
 * Add task center specific system messages to the welcome screen.
 * @copyright  Leo Feyer 2005-2011
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Controller
 */
class TaskMessages extends Backend
{

	/**
	 * Generate a task overview
	 * @return string
	 */
	public function listTasks()
	{
		$tasksReg = 0;
		$tasksNew = 0;
		$tasksDue = 0;

		$this->import('BackendUser', 'User');
		$objTemplate = new BackendTemplate('be_task_list');

		$objTask = $this->Database->prepare("SELECT t.deadline, s.status, s.assignedTo FROM tl_task t LEFT JOIN tl_task_status s ON t.id=s.pid AND s.tstamp=(SELECT MAX(tstamp) FROM tl_task_status ts WHERE ts.pid=t.id)" . (!$this->User->isAdmin ? " WHERE (t.createdBy=? OR s.assignedTo=?)" : ""))
								  ->execute($this->User->id, $this->User->id);

		if ($objTask->numRows) 
		{
			$time = time();

			while ($objTask->next())
			{
				if ($objTask->status == 'completed')
				{
					continue;
				}

				if ($objTask->deadline <= $time)
				{
					++$tasksDue;
				}
				elseif ($objTask->status == 'created' && $objTask->assignedTo == $this->User->id)
				{
					++$tasksNew;
				}
				else
				{
					++$tasksReg;
				}
			}

			if ($tasksReg > 0)
			{
				$objTemplate->tasksCur = sprintf($GLOBALS['TL_LANG']['MSC']['tasksCur'], $tasksReg);
			}

			if ($tasksNew > 0)
			{
				$objTemplate->tasksNew = sprintf($GLOBALS['TL_LANG']['MSC']['tasksNew'], $tasksNew);
			}

			if ($tasksDue > 0)
			{
				$objTemplate->tasksDue = sprintf($GLOBALS['TL_LANG']['MSC']['tasksDue'], $tasksDue);
			}
		}

		return $objTemplate->parse();
	}
}

?>