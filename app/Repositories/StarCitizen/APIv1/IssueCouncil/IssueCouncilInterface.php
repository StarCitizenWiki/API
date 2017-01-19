<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 12:54
 */

namespace app\Repositories\StarCitizen\APIv1\IssueCouncil;


interface IssueCouncilInterface
{
    /**
     * @TODO API unklar
     * https://robertsspaceindustries.com/community/issue-council/api/issue/list
     * @return string json
     */
    public function getIssues();

    /**
     * @TODO API unklar
     * https://robertsspaceindustries.com/community/issue-council/api/module/list
     * @return string json
     */
    public function listModules();

    /**
     * @TODO API unklar
     * https://robertsspaceindustries.com/community/issue-council/api/reporter/get
     * @return string json
     */
    public function getReporter();

    /**
     * @TODO API unklar
     * https://robertsspaceindustries.com/community/issue-council/api/module/get
     * @return string json
     */
    public function getModule();
}