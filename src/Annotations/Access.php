<?php
declare(strict_types=1);
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 14.01.19
 */

namespace GepurIt\AccessManagerBundle\Annotations;

/**
 * @Annotation
 *
 * Class Access
 * @package AppBundle\Annotations
 */
class Access
{
    /**
     * Supported permissions levels: READ, WRITE, DELETE, APPROVE
     *
     * @var string $permissionLevel
     */
    public $level = 'READ';

    /** @var string $resource */
    public $resource;

    /** @var string */
    public $message = 'Access Denied.';
}
