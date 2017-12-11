<?php

class NixFifty_ThreadLimit_XenForo_Model_Forum extends XFCP_NixFifty_ThreadLimit_XenForo_Model_Forum
{
    public function canPostThreadInForum(array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
    {
        $parent = parent::canPostThreadInForum($forum, $errorPhraseKey, $nodePermissions, $viewingUser);
        $this->standardizeViewingUserReferenceForNode($forum['node_id'], $viewingUser, $nodePermissions);

        $threadLimitCount = XenForo_Permission::hasContentPermission($nodePermissions, 'threadLimit');
        $timeFrame = XenForo_Permission::hasContentPermission($nodePermissions, 'threadLimitTime');

        if ($threadLimitCount === 0 OR $timeFrame === 0)
        {
            return $parent;
        }

        if ($parent)
        {
            $minPostDate = XenForo_Application::$time - ($timeFrame * 60);
            $createdThreadsInTimeFrame = $this->quickCountThreadsFromUserInNode($viewingUser['user_id'], $forum['node_id'], $minPostDate);

            if ($threadLimitCount <= $createdThreadsInTimeFrame)
            {
                $errorPhraseKey = 'nf_threadlimit_created_max_possible_threads_in_time_limit';
                return false;
            }
        }

        return $parent;
    }

    protected function quickCountThreadsFromUserInNode($userId, $nodeId, $postData)
    {
        return $this->_getDb()->fetchOne('
            SELECT COUNT(*)
            FROM xf_thread
            WHERE node_id = ?
            AND user_id = ?
            AND post_date > ?
        ', [$nodeId, $userId, $postData]);
    }
}