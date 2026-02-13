<div class="mx-auto flex w-[1200px] max-sm:w-full max-sm:px-4 flex-col gap-5 my-10 max-sm:my-6">
    <div class="flex flex-row  max-sm:gap-3 items-center justify-between">
        <h2 class="text-2xl font-bold sm:text-2xl md:text-3xl max-sm:text-xl">Lanzamientos</h2>
        <a href="{{ url('/productos') }}"
            class="text-primary-orange uppercase border-primary-orange hover:bg-primary-orange flex h-[41px] max-sm:h-[36px] w-[127px] max-sm:w-[100px] items-center justify-center border text-base max-sm:text-sm font-semibold transition duration-300 rounded-sm hover:text-white">
            Ver todos
        </a>
    </div>

    <div class="grid grid-cols-4 max-sm:grid-cols-1 gap-5 max-sm:gap-4">
           @foreach ($productos as $producto)
            <a href="{{ "/p/" . $producto->code }}"
                class="transition transform hover:-translate-y-1 hover:shadow-lg duration-300 h-[420px] flex flex-col w-[288px] max-sm:w-full rounded-sm border border-[#DEDFE0]">
                <div class="h-full flex flex-col">
                    <div class="relative min-h-[287px] max-sm:h-[200px]">
                        <img src="{{ $producto->imagen_final }}" 
                             alt="{{ $producto->name }}"
                             class="w-full h-full object-cover rounded-t-sm">
                        <h2 class="absolute left-3 bottom-2 text-[14px] font-semibold uppercase text-primary-orange">
                            {{ $producto->categoria->name ?? '' }}
                        </h2>
                    </div>
                    <div class="h-1 bg-[#DEDFE0] mx-3"></div>
                    <div class="flex flex-col mt-3 h-full max-sm:p-3 px-3">
                        <h2 class="text-[14px] font-bold">{{ $producto->code }}</h2>
                        <p class="text-gray-800 text-[18px] max-sm:text-[14px] font-semibold transition-colors duration-300">
                            {{ $producto->name }}
                        </p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>