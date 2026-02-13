@extends('layouts.default')
@section('title', 'SR33 - ' . $producto->code)

@section('description', $producto->name ?? "")


@section('content')
    <div class="flex flex-col gap-10 max-sm:gap-6">
        <!-- Breadcrumb navigation -->
        <div class="hidden lg:block w-[1200px] mx-auto h-full mt-6 max-sm:hidden text-sm">
            <div class="text-black">
                <a href="{{ route('home') }}" class="hover:underline transition-all duration-300 font-bold">Inicio</a>
                <span class="">></span>
                <a href="{{ route('productos') }}"
                    class="hover:underline transition-all duration-300 font-bold">Productos</a>
                <span class="">></span>
                <a href="{{ route('productos', ['id' => $producto->categoria->id]) }}"
                    class="hover:underline transition-all duration-300 font-bold">{{ $producto->categoria->name ?? '' }}</a>
                <span class="">></span>
                <a href="{{"/" . $producto->code }}"
                    class="font-light hover:underline transition-all duration-300">{{ $producto->code ?? '' }}</a>
            </div>
        </div>

        <!-- Main content with sidebar and product detail -->
        <div class="flex flex-col lg:flex-row gap-6 w-[1200px] mx-auto max-sm:w-full max-sm:px-4 max-sm:gap-4">
            <!-- Sidebar (1/4 width) -->


            <!-- Product Detail (3/4 width) -->
            <div class="w-full max-sm:w-full">
                <div class="flex flex-col md:flex-row gap-5 max-sm:gap-4">
                    <!-- Image Gallery -->
                    <div class="w-full flex flex-row gap-5 max-sm:flex-col max-sm:mt-10">
                        <div
                            class="  gap-2 flex flex-col  max-sm:static max-sm:mt-4 max-sm:justify-start max-sm:gap-1.5 max-sm:order-2">
                            @foreach ($producto->imagenes as $imagen)
                                <div class="border border-gray-200 w-[78px] h-[78px] cursor-pointer hover:border-main-color rounded-sm max-sm:w-[60px] max-sm:h-[60px]
                                                                                                                                                                                                                                                                                                                                                                            {{ $loop->first ? 'border-main-color' : '' }}"
                                    onclick="changeMainImage('{{ $imagen->image }}', this)">
                                    <img src="{{ $imagen->image }}" alt="Thumbnail"
                                        class="w-full h-full object-cover rounded-sm">
                                </div>
                            @endforeach
                        </div>
                        <!-- Main Image -->
    <div class="flex items-center w-full justify-center h-[496px] max-sm:h-[280px] border rounded-sm">
    <img id="mainImage"
         src="{{ $producto->imagen_final }}"
         alt="{{ $producto->name }}"
         class="w-full h-full object-cover object-center rounded-sm">
</div>


                        <!-- Thumbnails -->

                    </div>

                    <!-- Product Info -->
                    <div class="w-full  flex flex-col min-h-full justify-between max-sm:w-full max-sm:mt-6">
                        <div>
                            <h3 class="text-primary-orange text-[16px] font-bold uppercase ">
                                {{$producto->categoria->name}}
                            </h3>
                            <h1
                                class="text-[28px] font-semibold leading-[1] max-sm:text-xl max-sm:leading-tight border-b  pb-4">
                                {{ $producto->name }}
                            </h1>

                            <div class="flex flex-col w-full gap-4 max-sm:py-8">


                                @if ($producto->desc)
                                    <p class="text-[16px]">{{$producto->desc}}</p>
                                @endif
                            </div>


                        </div>

                        <a href="{{ route('contacto', ['mensaje' => $producto->name]) }}"
                            class="w-full flex justify-center rounded-sm items-center bg-primary-orange text-white font-bold h-[41px] max-sm:h-[36px] max-sm:text-sm">
                            CONSULTAR
                        </a>
                    </div>
                </div>



                <!-- Productos relacionados -->
                <div class="py-20 max-sm:py-10">
                    <h2 class="text-[28px] font-bold mb-8 max-sm:text-xl max-sm:mb-6">Productos relacionados</h2>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 max-sm:grid-cols-1 max-sm:gap-4">
                        @forelse($productosRelacionados as $prodRelacionado)
                            <a href="{{ "/p/" . $prodRelacionado->code }}"
                                class=" transition transform hover:-translate-y-1 hover:shadow-lg duration-300
                                                                                                                                                                                                                                                                            h-[420px] max-sm:h-auto flex flex-col w-[288px] max-sm:w-full rounded-sm border border-[#DEDFE0]">
                                <div class="h-full flex flex-col">
 <div class="relative min-h-[287px] max-sm:h-[200px]">
    <img src="{{ $prodRelacionado->imagen_final }}"
         alt="{{ $prodRelacionado->name }}"
         class="w-full h-full object-contain rounded-t-sm">

    <h2 class="absolute left-3 bottom-2 text-[14px] font-semibold uppercase text-primary-orange">
        {{ $prodRelacionado->categoria->name ?? '' }}
    </h2>
</div>


                                    <div class="h-1 bg-[#DEDFE0] mx-3"></div>
                                    <div class="flex flex-col justify-evenly h-full max-sm:p-3 px-3">
                                        <div class="flex flex-row justify-between">
                                            <h3
                                                class="text-black group-hover:text-green-700 text-[16px] max-sm:text-[14px] transition-colors duration-300">
                                                Cod. Or.: {{ $prodRelacionado->code }}
                                            </h3>
                                            <h3
                                                class="text-primary-orange group-hover:text-green-700 text-[16px] max-sm:text-[14px] transition-colors duration-300">
                                                Cod. SR: {{ $prodRelacionado->code_sr }}
                                            </h3>
                                        </div>
                                        <p
                                            class="text-gray-800 text-[18px] max-sm:text-[14px] font-semibold transition-colors duration-300 ">
                                            {{ $prodRelacionado->name }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="col-span-3 py-8 text-center text-gray-500 max-sm:col-span-1 max-sm:py-6">
                                No hay productos relacionados disponibles.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function changeMainImage(src, thumbnail) {
            const mainImage = document.getElementById('mainImage');

            // Fade out effect
            mainImage.style.opacity = '0';

            // Change image after fade out completes
            setTimeout(() => {
                mainImage.src = src;

                // Fade in the new image
                mainImage.style.opacity = '1';

                // Update thumbnail borders
                document.querySelectorAll('.flex.gap-2 > div').forEach(thumb => {
                    thumb.classList.remove('border-main-color');
                });
                thumbnail.classList.add('border-main-color');
            }, 300);
        }

        // Ensure image is visible on initial load
        document.addEventListener('DOMContentLoaded', () => {
            const mainImage = document.getElementById('mainImage');
            mainImage.style.opacity = '1';
        });
    </script>

    <style>
        #mainImage {
            opacity: 0;
        }
    </style>
@endsection