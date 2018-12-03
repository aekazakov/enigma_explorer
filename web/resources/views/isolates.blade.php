@extends('template')

@section('title', 'Isolates')

@section('content')
    @component('searchBox')
      Search isolates
    @endcomponent
    <div class="row my-4">
      <h1>Isolates</h1>
    </div>
    <div class="row my-4" style="font-size:1.2em;">
      <div class="col">
        <p>Search for isolates by isolates ID, pylogenic orders or closest relatives. Can't remember Latin? The search box accepts partial word and would give hints.</p>
        <p>More accurate search? Hit the <em>Advance Search</em> button to start!</p>
        <p>For questions and suggestions, please contact <a href="mailto:lmlui@lbl.gov">Lauren Lui</a></p>
      </div>
    </div>
@endsection
