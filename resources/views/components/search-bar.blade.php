<div class="w-full h-auto min-h-[195px] max-lg:min-h-0 bg-black  flex items-center py-6 max-lg:py-4">
    <form action="{{ route('productos') }}" method="GET" id="searchForm"
        class="flex flex-col lg:flex-row gap-6 max-sm:gap-4 w-[1200px] max-xl:w-full max-xl:px-6 max-lg:px-4 max-sm:px-4 mx-auto h-auto lg:h-[123px] items-start lg:items-center">

        <!-- Sección: Por vehículo / Código -->
        <div class="flex flex-col w-full lg:w-full gap-4 max-sm:gap-3">
            <h2 class="text-[24px] max-md:text-[20px] max-sm:text-[18px] font-bold  border-b pb-1 text-white">
                Por vehículo / Código
            </h2>

            <!-- Contenedor de campos -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4 max-sm:gap-3">

                
                      <div class="flex flex-col gap-2 relative">
                    <label for="tipo" class="text-[16px] max-sm:text-[14px] text-white ">Categoría</label>
                    <div class="relative">
                        <select
                            class="rounded-sm bg-white p-2 pr-2 outline-transparent focus:outline focus:outline-primary-orange transition duration-300 w-full text-sm max-sm:text-xs"
                            name="tipo" id="tipo">
                            <option value="">Seleccionar categoria</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ ($tipo ?? '') == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($tipo ?? '')
                            <a href="{{ route('productos', array_filter(request()->except('tipo'))) }}"
                                class="absolute right-5 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-500 transition duration-200"
                                title="Eliminar filtro">
                                <svg class="w-4 h-4 max-sm:w-3 max-sm:h-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Código Original -->
                <div class="flex flex-col gap-2 w-full">
                    <label for="codigo_original" class="text-[16px] max-sm:text-[14px] text-white ">Código SGB</label>
                    <div class="relative">
                        <input value="{{ $code ?? '' }}" type="text"
                            class="rounded-sm bg-white p-2 pr-2 outline-transparent focus:outline focus:outline-primary-orange transition duration-300 w-full text-sm max-sm:text-xs"
                            id="codigo_original" name="code" placeholder="Ingrese código original">
                        @if($code ?? '')
                            <a href="{{ route('productos', array_filter(request()->except('code'))) }}"
                                class="absolute right-5 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-500 transition duration-200"
                                title="Eliminar filtro">
                                <svg class="w-4 h-4 max-sm:w-3 max-sm:h-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Código SR33 -->
                <div class="flex flex-col gap-2 w-full">
                    <label for="codigo_sr" class="text-[16px] max-sm:text-[14px] text-white ">Código alternativo</label>
                    <div class="relative">
                        <input value="{{ $codeoem ?? '' }}" type="text"
                            class="rounded-sm bg-white p-2 pr-2 outline-transparent focus:outline focus:outline-primary-orange transition duration-300 w-full text-sm max-sm:text-xs"
                            id="codigo_oem" name="code_oem" placeholder="Ingrese código sr33">
                        @if($codeoem ?? '')
                            <a href="{{ route('productos', array_filter(request()->except('code_oem'))) }}"
                                class="absolute right-5 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-500 transition duration-200"
                                title="Eliminar filtro">
                                <svg class="w-4 h-4 max-sm:w-3 max-sm:h-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>


                <!-- Marca -->
                <div class="flex flex-col gap-2 w-full">
                    <label for="marca" class="text-[16px] max-sm:text-[14px] text-white ">Marca</label>
                    <div class="relative">
                        <select
                            class="rounded-sm bg-white p-2 pr-2 outline-transparent focus:outline focus:outline-primary-orange transition duration-300 w-full text-sm max-sm:text-xs"
                            name="marca" id="marca">
                            <option value="">Elegir marca</option>
                            @foreach ($marcas as $marcaItem)
                                <option value="{{ $marcaItem->id }}" {{ ($marca ?? '') == $marcaItem->id ? 'selected' : '' }}>
                                    {{ $marcaItem->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($marca ?? '')
                            <a href="{{ route('productos', array_filter(request()->except('marca'))) }}"
                                class="absolute right-5 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-500 transition duration-200"
                                title="Eliminar filtro">
                                <svg class="w-4 h-4 max-sm:w-3 max-sm:h-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Modelo -->
                <div class="flex flex-col gap-2 w-full">
                    <label for="modelo" class="text-[16px] max-sm:text-[14px] text-white ">Modelo</label>
                    <div class="relative">
                        <select
                            class="rounded-sm bg-white p-2 pr-2 outline-transparent focus:outline focus:outline-primary-orange transition duration-300 w-full text-sm max-sm:text-xs"
                            name="modelo" id="modelo">
                            <option value="">Elegir modelo</option>
                            @foreach ($modelos as $modeloItem)
                                <option value="{{ $modeloItem->id }}" {{ ($modelo ?? '') == $modeloItem->id ? 'selected' : '' }}>
                                    {{ $modeloItem->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($modelo ?? '')
                            <a href="{{ route('productos', array_filter(request()->except('modelo'))) }}"
                                class="absolute right-5 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-500 transition duration-200"
                                title="Eliminar filtro">
                                <svg class="w-4 h-4 max-sm:w-3 max-sm:h-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-2 relative">
                    <label for="tipo" class="text-[16px] max-sm:text-[14px] text-white ">Motor</label>
                    <div class="relative">
                        <select
                            class="rounded-sm bg-white p-2 pr-2 outline-transparent focus:outline focus:outline-primary-orange transition duration-300 w-full text-sm max-sm:text-xs"
                            name="motor" id="motor">
                            <option value="">Elegir el motor</option>
                            @foreach ($motores as $motorx)
                                <option value="{{ $motorx->id }}" {{ ($motor ?? '') == $motorx->id ? 'selected' : '' }}>
                                    {{ $motorx->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($motor ?? '')
                            <a href="{{ route('productos', array_filter(request()->except('motor'))) }}"
                                class="absolute right-5 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-500 transition duration-200"
                                title="Eliminar filtro">
                                <svg class="w-4 h-4 max-sm:w-3 max-sm:h-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

          
            </div>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('searchForm');
    const selects = form.querySelectorAll('select');
    const inputs = form.querySelectorAll('input[type="text"]');
    let searchTimeout;

    // Para los select, enviar inmediatamente al cambiar
    selects.forEach(select => {
        select.addEventListener('change', function() {
            form.submit();
        });
    });

    // Para los inputs de texto, usar timeout de 800ms
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                form.submit();
            }, 800);
        });
    });
});
</script>