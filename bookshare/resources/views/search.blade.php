@extends('layouts.master')

@section('title', 'Search for Textbooks')

@section('content')
@parent

<div class='search-form'>
	<div>
		<h2>Search for Textbooks</h2>
		<form method="GET" action="results" accept-charset="UTF-8">
			<input type="text" name="search" placeholder="Search by Name, Author, ISBN or Faculty"><br>
			<input type="hidden" name="_token" value="{{ csrf_token() }}">
			<input type="submit" value="Search">
		</form>
	</div>
</div>

@endsection