<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 12:54
 */

namespace App\Repositories\StarCitizen\Interfaces;

/**
 * Interface IssueCouncilInterface
 *
 * @package App\Repositories\StarCitizen\ApiV1\IssueCouncil
 */
interface IssueCouncilRepositoryInterface
{
    /**
     * @TODO Interfaces unklar
     * https://robertsspaceindustries.com/community/issue-council/api/issue/list
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getIssues();

    /**
     * @TODO Interfaces unklar
     * https://robertsspaceindustries.com/community/issue-council/api/module/list
     *
     * @return string json
     */
    public function listModules();

    /**
     * @TODO Interfaces unklar
     * https://robertsspaceindustries.com/community/issue-council/api/reporter/get
     *
     * @return string json
     */
    public function getReporter();

    /**
     * @TODO Interfaces unklar
     * https://robertsspaceindustries.com/community/issue-council/api/module/get
     *
     * @return string json
     */
    public function getModule();
}
