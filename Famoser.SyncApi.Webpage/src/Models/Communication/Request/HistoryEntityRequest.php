<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:16
 */

namespace Famoser\SyncApi\Models\Communication\Request;


use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;

class HistoryEntityRequest extends BaseRequest
{
    /* @var string $Id type_of:guid */
    public $Id;

    /* @var string[] $VersionIds type_of:guid[] */
    public $VersionIds;
}
