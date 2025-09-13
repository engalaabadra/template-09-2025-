<?php
namespace App\Repositories\Eloquent;

interface EloquentRepositoryInterface
{
   public function getData($model );
   public function show($id,$model );
}

