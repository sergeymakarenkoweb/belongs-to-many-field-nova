<?php

namespace Benjacho\BelongsToManyField\Http\Controllers;

use App\Models\Devices\Color;
use Epartment\NovaDependencyContainer\HasDependencies;
use Laravel\Nova\Http\Requests\NovaRequest;

class ResourceController
{
    public function index(NovaRequest $request, $parent, $relationship, $optionsLabel, $dependsOnValue = null, $dependsOnKey = null)
    {
        $resourceClass = $request->newResource();
        $field = $resourceClass
            ->availableFields($request)
            ->where('component', 'BelongsToManyField')
            ->where('attribute', $relationship)
            ->first();
        $query = $field->resourceClass::newModel();

        $queryResult = $field->resourceClass::relatableQuery($request, $query);

        if($dependsOnValue){
          $queryResult = $queryResult->where($dependsOnKey, $dependsOnValue);
        }
        return $queryResult->get()
            ->mapInto($field->resourceClass)
            ->filter(function ($resource) use ($request, $field) {
                return $request->newResource()->authorizedToAttach($request, $resource->resource);
            })->map(function ($resource) use ($optionsLabel) {
                return [
                    'id' => $resource->id,
                    $optionsLabel => $resource->title(),
                    'value' => $resource->getKey(),
                ];
            })
            ->values();
    }

    public function dependsOnField(NovaRequest $request, $parent, $relationship, $optionsLabel, $dependsOnField, $dependsOnValue)
    {
        $resourceClass = $request->newResource();
        $availableFields = $resourceClass->availableFields($request);
        $field = $availableFields
            ->where('component', 'BelongsToManyField')
            ->where('attribute', $relationship)
            ->first();

        $sourceField = $availableFields
            ->where('attribute', $dependsOnField)
            ->first();
        $sourceModel = $sourceField->modelClass::find($dependsOnValue);

        $colors = ($field->optionResolveCallback)($sourceModel);

        return $colors->mapInto($field->resourceClass)
            ->filter(
                function ($resource) use ($request, $field) {
                    return $request->newResource()->authorizedToAttach($request, $resource->resource);
                }
            )->map(
                function ($resource) use ($optionsLabel) {
                    return [
                        'id' => $resource->id,
                        $optionsLabel => $resource->title(),
                        'value' => $resource->getKey(),
                    ];
                }
            )
            ->values();
    }
}
