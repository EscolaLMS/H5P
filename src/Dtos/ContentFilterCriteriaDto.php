<?php

namespace EscolaLms\HeadlessH5P\Dtos;

use EscolaLms\Core\Dtos\Contracts\InstantiateFromRequest;
use EscolaLms\Core\Dtos\CriteriaDto;
use EscolaLms\Core\Repositories\Criteria\Primitives\EqualCriterion;
use EscolaLms\Core\Repositories\Criteria\Primitives\LikeCriterion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ContentFilterCriteriaDto extends CriteriaDto implements InstantiateFromRequest
{
    public static function instantiateFromRequest(Request $request): self
    {
        $criteria = new Collection();

        if ($request->has('title')) {
            $criteria->push(new LikeCriterion('hh5p_contents.title', $request->input('title')));
        }
        if ($request->has('library_id')) {
            $criteria->push(new EqualCriterion('hh5p_contents.library_id', $request->input('library_id')));
        }

        return new self($criteria);
    }
}
