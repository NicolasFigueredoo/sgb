@extends('layouts.default')

@section('title', 'SGB - Contacto')

@section('description', $metadatos->description ?? '')
@section('keywords', $metadatos->keywords ?? '')

@push('head')
    <meta name="description" content="{{ $metadatos->description ?? '' }}">
    <meta name="keywords" content="{{ $metadatos->keywords ?? '' }}">
@endpush

@section('content')

    <div class="mx-auto flex w-full max-w-[1200px] flex-col gap-6 px-4 md:gap-10 md:px-0">

        <div class="flex w-full text-black pt-6 text-sm mb-6">
            <a href="/" class="font-bold">Inicio</a>
            <span class="mx-1">></span>
            <p class="text-black/80">Contacto</p>
        </div>

        {{-- Mensaje de éxito --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6" role="alert">
                <div class="flex">
                    <div class="py-1">
                        <svg class="fill-current h-6 w-6 text-green-500 mr-4" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20">
                            <path
                                d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold">¡Éxito!</p>
                        <p class=" text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Mensaje de error general --}}
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6" role="alert">
                <div class="flex">
                    <div class="py-1">
                        <svg class="fill-current h-6 w-6 text-red-500 mr-4" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20">
                            <path
                                d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm1.41-1.41A8 8 0 1 0 15.66 4.34 8 8 0 0 0 4.34 15.66zm9.9-8.49L11.41 10l2.83 2.83-1.41 1.41L10 11.41l-2.83 2.83-1.41-1.41L8.59 10 5.76 7.17l1.41-1.41L10 8.59l2.83-2.83 1.41 1.41z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold">Error</p>
                        <p class="text-sm">Por favor, revisa los campos del formulario.</p>
                    </div>
                </div>
            </div>
        @endif

        <h1 class="text-[32px] font-bold">Contacto</h1>

        <div class="flex flex-col gap-8 md:flex-row md:gap-0">
            {{-- Contacto info --}}
            <div class="mb-6 flex w-full flex-col gap-4 md:mb-0 md:w-1/3">
                @php
                    $datos = [
                        [
                            'name' => $contacto->location ?? '',
                            'icon' =>
                                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff120b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin-icon lucide-map-pin h-6 w-6"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>',
                            'href' => 'https://maps.google.com/?q=' . urlencode($contacto->location),
                        ],

                        [
                            'name' => $contacto->mail ?? '',
                            'icon' =>
                                '<svg xmlns="http://www.w3.org/2000/svg" width="21" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff120b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-icon lucide-mail"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>',
                            'href' => 'mailto:' . $contacto->mail ?? '',
                        ],
                        [
                            'name' => $contacto->wp ?? '',
                            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                                                                                                                                                                                                                      <path fill-rule="evenodd" clip-rule="evenodd" d="M14.5823 11.985C14.3328 11.8608 13.1095 11.2625 12.8817 11.1792C12.6539 11.0967 12.4881 11.0558 12.3215 11.3042C12.1557 11.5508 11.6793 12.1092 11.5344 12.2742C11.3887 12.44 11.2438 12.46 10.9952 12.3367C10.7465 12.2117 9.94429 11.9508 8.99391 11.1075C8.25453 10.4509 7.75464 9.64002 7.60978 9.39169C7.46492 9.14419 7.59387 9.01002 7.71863 8.88669C7.83084 8.77585 7.96732 8.59752 8.09209 8.45335C8.21685 8.30835 8.25788 8.20502 8.34078 8.03919C8.42451 7.87419 8.38265 7.73002 8.31985 7.60585C8.25788 7.48169 7.7605 6.26252 7.55284 5.76669C7.35104 5.28419 7.14589 5.35003 6.99349 5.34169C6.8478 5.33503 6.682 5.33336 6.51621 5.33336C6.35041 5.33336 6.08079 5.39503 5.85303 5.64336C5.62444 5.89086 4.98219 6.49002 4.98219 7.70919C4.98219 8.92752 5.87313 10.105 5.99789 10.2709C6.12266 10.4359 7.75213 12.9375 10.2482 14.01C10.8428 14.265 11.3058 14.4175 11.6667 14.5308C12.2629 14.72 12.8055 14.6933 13.2342 14.6292C13.7115 14.5583 14.7063 14.03 14.9139 13.4517C15.1208 12.8733 15.1208 12.3775 15.0588 12.2742C14.9968 12.1708 14.831 12.1092 14.5815 11.985H14.5823ZM10.0423 18.1542H10.0389C8.55634 18.1544 7.10099 17.7578 5.8254 17.0058L5.52396 16.8275L2.39062 17.6458L3.22712 14.6058L3.03035 14.2942C2.20149 12.9811 1.76286 11.4615 1.76512 9.91085C1.7668 5.36919 5.47958 1.6742 10.0456 1.6742C12.2562 1.6742 14.3345 2.53253 15.897 4.08919C16.6676 4.85301 17.2785 5.76133 17.6941 6.7616C18.1098 7.76188 18.322 8.83425 18.3186 9.91668C18.3169 14.4583 14.6041 18.1542 10.0423 18.1542ZM17.086 2.9067C16.1634 1.98247 15.0657 1.24965 13.8564 0.7507C12.6472 0.251754 11.3505 -0.00339687 10.0414 3.41479e-05C4.55347 3.41479e-05 0.0854091 4.44586 0.0837344 9.91002C0.0811914 11.649 0.539563 13.3578 1.4126 14.8642L0 20L5.27861 18.6217C6.73884 19.4134 8.37519 19.8283 10.0381 19.8283H10.0423C15.5302 19.8283 19.9983 15.3825 20 9.91752C20.004 8.61525 19.7485 7.32511 19.2484 6.12172C18.7482 4.91833 18.0132 3.82559 17.086 2.9067Z" fill="#FF120B"/>
                                                                                                                                                                                                                                    </svg>',
                            'href' => 'https://wa.me/' . preg_replace('/\s+/', '', $contacto->wp),
                        ],
                    ];
                @endphp

                @foreach ($datos as $dato)
                    <a href="{{ $dato['href'] }}" target="_blank"
                        class="flex flex-row items-start gap-3 transition-opacity hover:opacity-80">
                        {!! $dato['icon'] !!}
                        <p class="text-base text-[#74716A] max-w-4/7">
                            {{ $dato['name'] }}
                        </p>
                    </a>
                @endforeach
            </div>

            {{-- Formulario --}}
            <form id="contactForm" method="POST" action="{{ route('send.contact') }}"
                class="grid w-full grid-cols-1 gap-6 sm:grid-cols-2 sm:gap-x-5 sm:gap-y-10 md:w-2/3">
                @csrf
                <div class="flex flex-col gap-2 sm:gap-3">
                    <label for="name" class="text-base text-[#74716A]">Nombre y Apellido*</label>
                    <input required type="text" name="name" id="name"
                        class="h-[44px] w-full border border-[#EEEEEE] pl-3 rounded-sm" value="{{ old('name') }}">
                    @error('name')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-2 sm:gap-3">
                    <label for="email" class="text-base text-[#74716A]">Email*</label>
                    <input required type="email" name="email" id="email"
                        class="h-[44px] w-full border border-[#EEEEEE] pl-3 rounded-sm" value="{{ old('email') }}">
                    @error('email')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-2 sm:gap-3">
                    <label for="celular" class="text-base text-[#74716A]">Celular*</label>
                    <input required type="text" name="celular" id="celular"
                        class="h-[44px] w-full border border-[#EEEEEE] pl-3 rounded-sm" value="{{ old('celular') }}">
                    @error('celular')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-2 sm:gap-3">
                    <label for="empresa" class="text-base text-[#74716A]">Empresa</label>
                    <input required type="text" name="empresa" id="empresa"
                        class="h-[44px] w-full border border-[#EEEEEE] pl-3 rounded-sm" value="{{ old('empresa') }}">
                    @error('empresa')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-2  sm:gap-3">
                    <label for="mensaje" class="text-base text-[#74716A]">Mensaje</label>
                    <textarea required name="mensaje" id="mensaje" class="h-[150px] w-full border border-[#EEEEEE] pt-2 pl-3 rounded-sm">{{ $mensaje ? 'Buenas tardes queria recibir informacion acerca de ' . $mensaje : '' }}</textarea>
                    @error('mensaje')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-between items-end">
                    <p class="text-base text-[#74716A] min-h-[34px]">*Campos obligatorios</p>
                    <button form="contactForm" type="submit"
                        class="bg-primary-orange font-bold min-h-[41px] w-[185px] uppercase text-white rounded-sm">Enviar
                        consulta</button>
                </div>
            </form>
        </div>

        {{-- Mapa --}}
        <div class="mt-4 w-full">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3280.9649102517105!2d-58.50300742341466!3d-34.68083507292675!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x95bcc9258353df35%3A0xcf81ab3f4ab5bb56!2sPedernera%201341%2C%20B1768CLU%20Villa%20Madero%2C%20Provincia%20de%20Buenos%20Aires!5e0!3m2!1ses-419!2sar!4v1755260624749!5m2!1ses-419!2sar"
                class="w-full h-[500px] rounded-sm grayscale" style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>

    <script>
        // Auto-cerrar mensaje después de 5 segundos
        setTimeout(function() {
            const successAlert = document.querySelector('.bg-green-100');
            if (successAlert) {
                successAlert.style.display = 'none';
            }
        }, 5000);
    </script>
@endsection
