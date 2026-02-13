@extends('layouts.default')

@section('title', 'SGB - Productos')


@section('description', $metadatos->description ?? '')
@section('keywords', $metadatos->keywords ?? '')

@section('content')
    <div class="mx-auto flex w-full max-w-[1200px]">
        <div class="absolute flex text-white pt-2 text-sm z-30">
            <a href="/" class="font-bold">Inicio</a>
            <span class="mx-1">></span>
            <p>Productos</p>
        </div>
    </div>
    <x-search-bar :categorias="$categorias" :marcas="$marcas" :modelos="$modelos" :motores="$motores" />
    <div class="w-[1200px] mx-auto grid grid-cols-4 min-h-[50vh] my-20">
        @foreach ($categorias as $categoria)
       <a
  href="{{ route('productos', ['tipo' => $categoria->id]) }}"
  class="flex flex-col items-center justify-start w-[288px] h-fit gap-5"
>
  <div class="w-full h-[288px] overflow-hidden border border-[#DEDFE0] hover:shadow-lg transition duration-300">
    <img
      src="{{ $categoria->image }}"
      alt="{{ $categoria->name }}"
      class="w-full h-full object-cover object-center hover:scale-105 transition duration-300"
    >
  </div>
  <span class="text-lg uppercase">{{ $categoria->name }}</span>
</a>

        @endforeach
    </div>
@endsection
