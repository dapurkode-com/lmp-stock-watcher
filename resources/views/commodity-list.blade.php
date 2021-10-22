@extends('adminlte::page')

@section('title', 'Commodity')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-light">
                <i class="fas fa-balance-scale"></i>
                Commodities
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active">Commodities</li>
            </ol>
        </div>
    </div>
</div>
@stop

@section('content')
    <commodity-list></commodity-list>
@stop
