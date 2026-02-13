@extends('layouts.default')
@section('title', 'Productos - SR33')

@section('description', $metadatos->description ?? '')
@section('keywords', $metadatos->keywords ?? '')

@section('content')
    <div class="mx-auto flex w-full max-w-[1200px]">
        <div class="absolute flex text-white pt-2 text-sm z-30">
            <a href="/" class="font-bold">Inicio</a>
            <span class="mx-1">></span>
            <a href={{ route('productos.categorias') }} class="font-bold">Productos</a>
            <span class="mx-1">></span>
            <p>{{ $productos[0]->categoria->name }}</p>
        </div>
    </div>
    <div class="flex flex-col gap-10 max-sm:gap-6">


        <x-search-bar :categorias="$categorias" :marcas="$marcas" :modelos="$modelos" :tipo="$tipo" :code="$code"
            :codeoem="$code_oem" :marca="$marca" :modelo="$modelo" :motores="$motores" :motor="$motor" />

        <!-- Main content with sidebar and products -->
        <div class="flex flex-col lg:flex-row gap-6 max-sm:gap-4 w-[1200px] max-sm:w-full max-sm:px-4 mx-auto">

            {{-- Sidebar with categories --}}


            <div class="w-full mb-10">
                <div class="grid grid-cols-1 md:grid-cols-4 max-sm:grid-cols-1 gap-6 max-sm:gap-4">
                @forelse($productos as $producto)
                        <a href="{{ '/p/' . $producto->code }}"
                            class="transition transform hover:-translate-y-1 hover:shadow-lg duration-300 h-[430px] flex flex-col max-sm:w-full rounded-sm border border-[#DEDFE0]">
                            <div class="w-full h-[287px] overflow-hidden relative">
                                <!-- ✅ USAR imagen_final QUE YA TIENE FALLBACK -->
                                <img src="{{ $producto->imagen_final }}" 
                                     alt="{{ $producto->name }}"
                                     class="w-full h-[287px] object-contain p-2"
                                     onerror="this.src='{{ asset('storage/images/logo.png') }}'">

                                <p class="absolute bottom-3 px-4 text-sm font-bold text-[#FF120B] uppercase">
                                    {{ $producto->categoria->name }}
                                </p>
                                <hr class="flex border-t-1 border-[#DEDFE0] w-[86%] absolute bottom-0 right-0 left-0 mx-4">
                            </div>
                            <div class="flex flex-col flex-1 p-4 h-[143px]">
                                <p class="text-sm font-bold text-gray-800">{{ $producto->code }}</p>
                                <p class="text-lg text-gray-800 line-clamp-4">{{ $producto->name }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-3 max-sm:col-span-1 py-8 max-sm:py-6 text-center text-gray-500">
                            No hay productos disponibles en esta categoría.
                        </div>
                    @endforelse
                </div>

                {{-- Enlaces de paginación --}}
                @if ($productos->hasPages())
                    <div class="mt-8 max-sm:mt-6 flex flex-col justify-center my-10">
                        <div class="pagination-wrapper">
                            {{ $productos->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
