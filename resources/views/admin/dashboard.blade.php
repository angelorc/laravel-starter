@extends('layouts.backend')

@section('title', 'Dashboard')

@section('page_script')    

@endsection

@section('page_header')
<!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ action('Admin\HomeController@index') }}"><i class="fa fa-dashboard"></i> Home</a></li>
      </ol>
    </section>
@endsection

@section('content')
	
@endsection
