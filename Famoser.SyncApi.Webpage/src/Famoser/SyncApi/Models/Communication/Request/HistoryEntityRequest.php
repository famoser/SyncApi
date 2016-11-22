<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:16
 */

namespace Famoser\SyncApi\Models\Communication\Request;


use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;

/**
 * a history entity request
 * specify the id of the entity you want to history from, and list the version ids you already have
 * @package Famoser\SyncApi\Models\Communication\Request
 */
class HistoryEntityRequest extends BaseRequest
{
    /* @var string $Id type_of:guid */
    public $Id;

    /* @var string[] $VersionIds type_of:guid[] */
    public $VersionIds;
}
