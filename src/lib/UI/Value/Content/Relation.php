<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\Content;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Relation as APIRelation;
use Ibexa\Core\Repository\Values\Content\Relation as CoreRelation;

/**
 * Extends original value object in order to provide additional fields.
 */
class Relation extends CoreRelation implements RelationInterface
{
    /**
     * Field definition name for the relation.
     * This will either come from destinationContentInfo OR sourceContentInfo depending upon if reverse relation or normal relation.
     *
     * @var string
     */
    protected $relationFieldDefinitionName;

    /**
     * The content type name for the relation.
     * This will either come from destinationContentInfo OR sourceContentInfo depending upon if reverse relation or normal relation.
     *
     * @var string
     */
    protected $relationContentTypeName;

    /**
     * Main location for the relation.
     * This will either come from destinationContentInfo OR sourceContentInfo depending upon if reverse relation or normal relation.
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Location
     */
    protected $relationLocation;

    /**
     * The name for the relation.
     * This will either come from destinationContentInfo OR sourceContentInfo depending upon if reverse relation or normal relation.
     *
     * @var string
     */
    protected $relationName;

    /**
     * Source location for the relation.
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Location
     */
    protected $resolvedSourceLocation;

    /**
     * Destination location for the relation.
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Location
     */
    protected $resolvedDestinationLocation;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Relation $relation
     * @param array $properties
     */
    public function __construct(APIRelation $relation, array $properties = [])
    {
        parent::__construct(get_object_vars($relation) + $properties);
    }

    /**
     * @return bool
     */
    public function isAccessible(): bool
    {
        return true;
    }
}

class_alias(Relation::class, 'EzSystems\EzPlatformAdminUi\UI\Value\Content\Relation');
