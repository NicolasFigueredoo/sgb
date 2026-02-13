import { router, useForm, usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';

const SearchBar = () => {
    const { categorias, marcas, modelos, motores, filters } = usePage().props;
    const debounceTimer = useRef(null);

    const { data, setData } = useForm({
        categoria: filters?.categoria || '',
        marca: filters?.marca || '',
        modelo: filters?.modelo || '',
        motor: filters?.motor || '',
        code: filters?.code || '',
        code_oem: filters?.code_oem || '',
    });

    // Sincronizar el estado cuando cambien los filtros desde el backend
    useEffect(() => {
        setData({
            categoria: filters?.categoria || '',
            marca: filters?.marca || '',
            modelo: filters?.modelo || '',
            code: filters?.code || '',
            code_oem: filters?.code_oem || '',
            motor: filters?.motor || '',
        });
    }, [filters]);

    // Función para ejecutar la búsqueda
    const performSearch = (searchData) => {
        // Limpiar filtros vacíos
        const cleanedFilters = {};
        Object.keys(searchData).forEach((key) => {
            if (searchData[key]) {
                cleanedFilters[key] = searchData[key];
            }
        });

        router.get(route('index.privada.productos'), cleanedFilters, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    // Efecto para búsqueda automática con debounce
    useEffect(() => {
        if (debounceTimer.current) {
            clearTimeout(debounceTimer.current);
        }

        debounceTimer.current = setTimeout(() => {
            performSearch(data);
        }, 500);

        // Cleanup
        return () => {
            if (debounceTimer.current) {
                clearTimeout(debounceTimer.current);
            }
        };
    }, [data.categoria, data.marca, data.modelo, data.motor, data.code, data.code_oem]);

    // Función para eliminar un filtro específico
    const removeFilter = (filterName) => {
        setData(filterName, '');
    };

    // Componente para el botón de eliminar filtro
    const ClearFilterButton = ({ filterValue, filterName }) => {
        if (!filterValue) return null;

        return (
            <button
                type="button"
                onClick={() => removeFilter(filterName)}
                className="absolute top-1/2 right-5 -translate-y-1/2 transform text-gray-400 transition duration-200 hover:text-red-500"
                title="Eliminar filtro"
            >
                <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        );
    };

    return (
        <div className="flex min-h-[195px] w-full items-center bg-black py-4 md:h-[195px] md:py-0">
            <div className="mx-auto flex h-auto w-full max-w-[1200px] flex-col items-center gap-6 px-4 py-4 md:h-[123px] md:w-[1200px] md:flex-row md:px-0 md:py-0">
                {/* Sección de vehículo/código */}
                <div className="flex w-full flex-col gap-4 md:w-full">
                    <h2 className="border-b pb-1 text-[20px] font-bold text-white md:text-[24px]">Por vehículo / Código</h2>

                    <div className="flex flex-col gap-4 md:flex-row">
                        
                        
                        {/* Categoria */}

                        <div className="flex w-full flex-col gap-2">
                            <label htmlFor="tipo" className="text-[14px] text-white md:text-[16px]">
                                Categoria
                            </label>
                            <div className="relative">
                                <select
                                    className="focus:outline-primary-orange w-full rounded-sm bg-white p-2 text-sm outline-transparent transition duration-300 focus:outline md:text-base"
                                    name="tipo"
                                    id="tipo"
                                    value={data.categoria}
                                    onChange={(e) => setData('categoria', e.target.value)}
                                >
                                    <option value="">Elegir la categoria</option>
                                    {categorias?.map((categoria) => (
                                        <option key={categoria.id} value={categoria.id}>
                                            {categoria.name}
                                        </option>
                                    ))}
                                </select>
                                <ClearFilterButton filterValue={data.categoria} filterName="tipo" />
                            </div>
                        </div>

                           {/* Código Original */}
                        <div className="flex w-full flex-col gap-2">
                            <label htmlFor="codigo_original" className="text-[14px] text-white md:text-[16px]">
                                Código SGB
                            </label>
                            <div className="relative">
                                <input
                                    type="text"
                                    className="focus:outline-primary-orange w-full rounded-sm bg-white p-2 text-sm outline-transparent transition duration-300 focus:outline md:text-base"
                                    id="codigo_original"
                                    name="codigo_original"
                                    placeholder="Ingrese código original"
                                    value={data.code}
                                    onChange={(e) => setData('code', e.target.value)}
                                />
                                <ClearFilterButton filterValue={data.code} filterName="code" />
                            </div>
                        </div>

                              {/* Código SR33 */}
                        <div className="flex w-full flex-col gap-2">
                            <label htmlFor="codigo_sr" className="text-[14px] text-white md:text-[16px]">
                                Código alternativo
                            </label>
                            <div className="relative">
                                <input
                                    type="text"
                                    className="focus:outline-primary-orange w-full rounded-sm bg-white p-2 text-sm outline-transparent transition duration-300 focus:outline md:text-base"
                                    id="codigo_sr"
                                    name="codigo_sr"
                                    placeholder="Ingrese código sr33"
                                    value={data.code_oem}
                                    onChange={(e) => setData('code_oem', e.target.value)}
                                />
                                <ClearFilterButton filterValue={data.code_oem} filterName="code_oem" />
                            </div>
                        </div>

                        {/* Marca */}
                        <div className="flex w-full flex-col gap-2">
                            <label htmlFor="marca" className="text-[14px] text-white md:text-[16px]">
                                Marca
                            </label>
                            <div className="relative">
                                <select
                                    className="focus:outline-primary-orange w-full rounded-sm bg-white p-2 text-sm outline-transparent transition duration-300 focus:outline md:text-base"
                                    name="marca"
                                    id="marca"
                                    value={data.marca}
                                    onChange={(e) => setData('marca', e.target.value)}
                                >
                                    <option value="">Elegir marca</option>
                                    {marcas?.map((marca) => (
                                        <option key={marca.id} value={marca.id}>
                                            {marca.name}
                                        </option>
                                    ))}
                                </select>
                                <ClearFilterButton filterValue={data.marca} filterName="marca" />
                            </div>
                        </div>

                        {/* Modelo */}
                        <div className="flex w-full flex-col gap-2">
                            <label htmlFor="modelo" className="text-[14px] text-white md:text-[16px]">
                                Modelo
                            </label>
                            <div className="relative">
                                <select
                                    className="focus:outline-primary-orange w-full rounded-sm bg-white p-2 text-sm outline-transparent transition duration-300 focus:outline md:text-base"
                                    name="modelo"
                                    id="modelo"
                                    value={data.modelo}
                                    onChange={(e) => setData('modelo', e.target.value)}
                                >
                                    <option value="">Elegir modelo</option>
                                    {modelos
                                        ?.filter((mod) => !data.marca || mod?.marca_id == data.marca)
                                        ?.map((modelo) => (
                                            <option key={modelo.id} value={modelo.id}>
                                                {modelo.name}
                                            </option>
                                        ))}
                                </select>
                                <ClearFilterButton filterValue={data.modelo} filterName="modelo" />
                            </div>
                        </div>
                        {/* Motor */}

                        <div className="flex w-full flex-col gap-2">
                            <label htmlFor="motor" className="text-[14px] text-white md:text-[16px]">
                                Motor
                            </label>
                            <div className="relative">
                                <select
                                    className="focus:outline-primary-orange w-full rounded-sm bg-white p-2 text-sm outline-transparent transition duration-300 focus:outline md:text-base"
                                    name="motor"
                                    id="motor"
                                    value={data.motor}
                                    onChange={(e) => setData('motor', e.target.value)}
                                >
                                    <option value="">Elegir motor</option>
                                    {motores?.map((motor) => (
                                        <option key={motor.id} value={motor.id}>
                                            {motor.name}
                                        </option>
                                    ))}
                                </select>
                                <ClearFilterButton filterValue={data.motor} filterName="motor" />
                            </div>
                        </div>

                     

                  

                    </div>
                </div>
            </div>
        </div>
    );
};

export default SearchBar;