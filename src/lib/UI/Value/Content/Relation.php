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
     */
    protected string $relationFieldDefinitionName;

    /**
     * The content type name for the relation.
     * This will either come from destinationContentInfo OR sourceContentInfo depending upon if reverse relation or normal relation.
     */
    protected string $relationContentTypeName;

    /**
     * Main location for the relation.
     * This will either come from destinationContentInfo OR sourceContentInfo depending upon if reverse relation or normal relation.
     */
    protected Location $relationLocation;

    /**
     * The name for the relation.
     * This will either come from destinationContentInfo OR sourceContentInfo depending upon if reverse relation or normal relation.
     */
    protected string $relationName;

    /**
     * Source location for the relation.
     */
    protected Location $resolvedSourceLocation;

    /**
     * Destination location for the relation.
     */
    protected Location $resolvedDestinationLocation;

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct(
        readonly APIRelation $relation,
        readonly array $properties = []
    ) {
        parent::__construct(get_object_vars($relation) + $properties);
    }

    public function isAccessible(): bool
    {
        return true;
    }
}
