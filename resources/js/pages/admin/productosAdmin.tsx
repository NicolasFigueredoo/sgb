import ProductosAdminRow from '@/components/productosAdminRow';
import { router, useForm, usePage } from '@inertiajs/react';
import { AnimatePresence, motion } from 'framer-motion';
import { useEffect, useState } from 'react';
import toast from 'react-hot-toast';
import Select from 'react-select';
import Dashboard from './dashboard';

export default function ProductosAdmin() {
    const { productos, categorias, marcas, modelos, motores } = usePage().props;

    const { data, setData, post, reset, errors } = useForm({
        name: '',
        code: '',
        code_oem: '',
        medidas: '',
        categoria_id: '',
        marca_id: '',
        unidad_pack: '',
        images: [],
        modelos: [],
        motores: [],
    });

    const [searchTerm, setSearchTerm] = useState('');
    const [createView, setCreateView] = useState(false);
    const [subirProductos, setSubirProductos] = useState(false);
    const [archivo, setArchivo] = useState();

    const [imagePreviews, setImagePreviews] = useState([]);
    const [modeloSelected, setModeloSelected] = useState([]);
    const [motorSelected, setMotorSelected] = useState([]);

    useEffect(() => {
        setData(
            'modelos',
            modeloSelected.map((m) => m.value),
        );
        setData(
            'motores',
            motorSelected.map((m) => m.value),
        );
    }, [modeloSelected, motorSelected]);

    const handleFileChange = (e) => {
        const files = Array.from(e.target.files);

        // Actualizar el form data con los archivos
        setData('images', files);

        // Crear previews de las imágenes
        const previews = files.map((file) => {
            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.onload = (e) =>
                    resolve({
                        file: file,
                        url: e.target.result,
                        name: file.name,
                        size: file.size,
                    });
                reader.readAsDataURL(file);
            });
        });

        // Actualizar el estado de previews
        Promise.all(previews).then(setImagePreviews);
    };

    const removeImage = (indexToRemove) => {
        const newImages = data.images.filter((_, index) => index !== indexToRemove);
        const newPreviews = imagePreviews.filter((_, index) => index !== indexToRemove);

        setData('images', newImages);
        setImagePreviews(newPreviews);
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        post(route('admin.productos.store'), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Producto creada correctamente');
                reset();
                setCreateView(false);
            },
            onError: (errors) => {
                toast.error('Error al crear producto');
                console.log(errors);
            },
        });
    };

    const importarProductos = (e) => {
        e.preventDefault();

        router.post(
            route('importarProductos'),
            { archivo },
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    toast.success('Productos importados correctamente');
                    reset();
                    setSubirProductos(false);
                },
                onError: (errors) => {
                    toast.error('Error al importar productos');
                    console.log(errors);
                },
            },
        );
    };

    // Manejadores para la paginación del backend
    const handlePageChange = (page) => {
        router.get(
            route('admin.productos'),
            {
                page: page,
                search: searchTerm,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    // Función para realizar la búsqueda
    const handleSearch = () => {
        router.get(
            route('admin.productos'),
            {
                search: searchTerm,
                page: 1, // Resetear a la primera página al buscar
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    return (
        <Dashboard>
            <div className="flex w-full flex-col p-6">
                <AnimatePresence>
                    {createView && (
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            className="fixed top-0 left-0 z-50 flex h-full w-full items-center justify-center bg-black/50 text-left"
                        >
                            <form onSubmit={handleSubmit} method="POST" className="relative rounded-lg bg-white text-black">
                                <div className="bg-primary-orange sticky top-0 flex flex-row items-center gap-2 rounded-t-lg p-4">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="28"
                                        height="28"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="#ffffff"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        className="lucide lucide-plus-icon lucide-plus"
                                    >
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg>
                                    <h2 className="text-2xl font-semibold text-white">Crear Producto</h2>
                                </div>

                                <div className="max-h-[60vh] w-[500px] overflow-y-auto rounded-md bg-white p-4">
                                    <div className="flex flex-col gap-4">
                                        <label htmlFor="ordennn">Orden</label>
                                        <input
                                            className="focus:outline-primary-orange rounded-md p-2 outline outline-gray-300 focus:outline"
                                            type="text"
                                            name="ordennn"
                                            id="ordennn"
                                            onChange={(e) => setData('order', e.target.value)}
                                        />
                                        <label htmlFor="nombree">Nombre</label>
                                        <input
                                            className="focus:outline-primary-orange rounded-md p-2 outline outline-gray-300 focus:outline"
                                            type="text"
                                            name="nombree"
                                            id="nombree"
                                            onChange={(e) => setData('name', e.target.value)}
                                        />

                                        <label htmlFor="code">Codigo</label>
                                        <input
                                            className="focus:outline-primary-orange rounded-md p-2 outline outline-gray-300 focus:outline"
                                            type="text"
                                            name="code"
                                            id="code"
                                            onChange={(e) => setData('code', e.target.value)}
                                        />

                                        <label htmlFor="code_oem">Codigo OEM</label>
                                        <input
                                            className="focus:outline-primary-orange rounded-md p-2 outline outline-gray-300 focus:outline"
                                            type="text"
                                            name="code_oem"
                                            id="code_oem"
                                            onChange={(e) => setData('code_oem', e.target.value)}
                                        />

                                        <label htmlFor="unidad">Unidad por pack</label>
                                        <input
                                            className="focus:outline-primary-orange rounded-md p-2 outline outline-gray-300 focus:outline"
                                            type="number"
                                            name="unidad"
                                            id="unidad"
                                            onChange={(e) => setData('unidad_pack', e.target.value)}
                                        />

                                        <label htmlFor="descuento">Descuento por oferta</label>
                                        <input
                                            className="focus:outline-primary-orange rounded-md p-2 outline outline-gray-300 focus:outline"
                                            type="number"
                                            name="descuento"
                                            id="descuento"
                                            onChange={(e) => setData('descuento', e.target.value)}
                                        />

                                        <label htmlFor="medidas">Medidas</label>
                                        <input
                                            className="focus:outline-primary-orange rounded-md p-2 outline outline-gray-300 focus:outline"
                                            type="text"
                                            name="medidas"
                                            id="medidas"
                                            onChange={(e) => setData('medidas', e.target.value)}
                                        />

                                        <label htmlFor="categoria">Categoria</label>
                                        <select
                                            className="focus:outline-primary-orange rounded-md p-2 outline outline-gray-300 focus:outline"
                                            onChange={(e) => setData('categoria_id', e.target.value)}
                                            name=""
                                            id=""
                                        >
                                            <option value="">Seleccionar Tipo de producto</option>
                                            {categorias.map((categoria) => (
                                                <option key={categoria.id} value={categoria.id}>
                                                    {categoria.name}
                                                </option>
                                            ))}
                                        </select>

                                        <label htmlFor="marca">Marcas</label>
                                        <select
                                            className="focus:outline-primary-orange rounded-md p-2 outline outline-gray-300 focus:outline"
                                            onChange={(e) => setData('marca_id', e.target.value)}
                                            name=""
                                            id=""
                                        >
                                            <option value="">Seleccionar marca</option>
                                            {marcas.map((marca) => (
                                                <option key={marca.id} value={marca.id}>
                                                    {marca.name}
                                                </option>
                                            ))}
                                        </select>

                                        <label htmlFor="modelo">Modelos</label>
                                        <Select
                                            options={modelos?.map((modelo) => ({
                                                value: modelo.id,
                                                label: modelo.name,
                                            }))}
                                            onChange={(options) => setModeloSelected(options)}
                                            className=""
                                            name="modelo"
                                            id="modelo"
                                            placeholder="Seleccionar modelos..."
                                            isMulti
                                        />

                                        <label htmlFor="motor">Motores</label>
                                        <Select
                                            options={motores?.map((motor) => ({
                                                value: motor.id,
                                                label: motor.name,
                                            }))}
                                            onChange={(options) => setMotorSelected(options)}
                                            className=""
                                            name="motor"
                                            id="motor"
                                            placeholder="Seleccionar motores..."
                                            isMulti
                                        />

                                        <label htmlFor="precio">Precio</label>
                                        <input
                                            className="focus:outline-primary-orange rounded-md p-2 outline outline-gray-300 focus:outline"
                                            type="number"
                                            name="precio"
                                            id="precio"
                                            onChange={(e) => setData('precio', e.target.value)}
                                        />

                                        <label>Imágenes del Producto</label>
                                        <input
                                            type="file"
                                            multiple
                                            accept="image/*"
                                            onChange={handleFileChange}
                                            className="file:bg-primary-orange w-full rounded border p-2 file:cursor-pointer file:rounded-full file:px-4 file:py-2 file:text-white"
                                        />
                                        {errors.images && <span className="text-red-500">{errors.images}</span>}
                                        {errors['images.*'] && <span className="text-red-500">{errors['images.*']}</span>}

                                        {/* Preview de imágenes */}
                                        {imagePreviews.length > 0 && (
                                            <div className="space-y-2">
                                                <h4>Imágenes seleccionadas ({imagePreviews.length})</h4>
                                                <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                                                    {imagePreviews.map((preview, index) => (
                                                        <div key={index} className="relative">
                                                            <img
                                                                src={preview.url}
                                                                alt={preview.name}
                                                                className="h-32 w-full rounded border object-cover"
                                                            />
                                                            <button
                                                                type="button"
                                                                onClick={() => removeImage(index)}
                                                                className="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-sm text-white hover:bg-red-600"
                                                            >
                                                                ×
                                                            </button>
                                                            <p className="mt-1 truncate text-xs text-gray-600">{preview.name}</p>
                                                            <p className="text-xs text-gray-500">{(preview.size / 1024 / 1024).toFixed(2)} MB</p>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                </div>
                                <div className="bg-primary-orange sticky bottom-0 flex justify-end gap-4 rounded-b-md p-4">
                                    <button
                                        type="button"
                                        onClick={() => setCreateView(false)}
                                        className="rounded-md border border-red-500 bg-red-500 px-2 py-1 text-white transition duration-300"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        className="hover:text-primary-orange rounded-md px-2 py-1 text-white outline outline-white transition duration-300 hover:bg-white"
                                    >
                                        Guardar
                                    </button>
                                </div>
                            </form>
                        </motion.div>
                    )}
                </AnimatePresence>
                <AnimatePresence>
                    {subirProductos && (
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            className="fixed top-0 left-0 z-50 flex h-full w-full items-center justify-center bg-black/50 text-left"
                        >
                            <form onSubmit={importarProductos} method="POST" className="relative rounded-lg bg-white text-black">
                                <div className="bg-primary-orange sticky top-0 flex flex-row items-center gap-2 rounded-t-lg p-4">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="28"
                                        height="28"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="#ffffff"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        className="lucide lucide-plus-icon lucide-plus"
                                    >
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg>
                                    <h2 className="text-2xl font-semibold text-white">Subir Producto</h2>
                                </div>

                                <div className="max-h-[60vh] w-[500px] overflow-y-auto rounded-md bg-white p-4">
                                    <div className="flex flex-col gap-4">
                                        <label htmlFor="archivo">Archivo</label>
                                        <input
                                            className="file:bg-primary-orange rounded-md p-2 file:cursor-pointer file:rounded-full file:px-4 file:py-2 file:text-white"
                                            type="file"
                                            name="archivo"
                                            id="archivo"
                                            onChange={(e) => setArchivo(e.target.files[0])}
                                        />
                                    </div>
                                </div>
                                <div className="bg-primary-orange sticky bottom-0 flex justify-end gap-4 rounded-b-md p-4">
                                    <button
                                        type="button"
                                        onClick={() => setSubirProductos(false)}
                                        className="rounded-md border border-red-500 bg-red-500 px-2 py-1 text-white transition duration-300"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        className="hover:text-primary-orange rounded-md px-2 py-1 text-white outline outline-white transition duration-300 hover:bg-white"
                                    >
                                        Subir
                                    </button>
                                </div>
                            </form>
                        </motion.div>
                    )}
                </AnimatePresence>

                <div className="mx-auto flex w-full flex-col gap-3">
                    <h2 className="border-primary-orange text-primary-orange text-bold w-full border-b-2 text-2xl">Productos</h2>
                    <div className="flex h-fit w-full flex-row gap-5">
                        <input
                            type="text"
                            placeholder="Buscar producto..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="w-full rounded-md border border-gray-300 px-3"
                        />
                        <button
                            onClick={handleSearch}
                            className="bg-primary-orange w-[200px] rounded px-4 py-1 font-bold text-white hover:bg-orange-400"
                        >
                            Buscar
                        </button>
                        <button
                            onClick={() => setCreateView(true)}
                            className="bg-primary-orange w-[300px] rounded px-4 py-1 font-bold text-white hover:bg-orange-400"
                        >
                            Crear Producto
                        </button>
                        <button
                            onClick={() => setSubirProductos(true)}
                            className="bg-primary-orange w-[300px] rounded px-4 py-1 font-bold text-white hover:bg-orange-400"
                        >
                            Subir productos
                        </button>
                    </div>
                    <div className="flex w-full justify-center">
                        <table className="w-full border text-left text-sm text-gray-500 rtl:text-right dark:text-gray-400">
                            <thead className="bg-gray-300 text-sm font-medium text-black uppercase">
                                <tr>
                                    <td className="text-center">ORDEN</td>
                                    <td className="text-center">CODIGO</td>
                                    <td className="text-center">CODIGO OEM</td>
                                    <td className="text-center">NOMBRE</td>
                                    <td className="text-center">CATEGORIA</td>

                                    <td className="text-center">DESTACADO</td>
                                    <td className="py-2 text-center">LANZAMIENTO</td>
                                    <td className="text-center">OFERTA</td>

                                    <td className="text-center">EDITAR</td>
                                </tr>
                            </thead>
                            <tbody className="text-center">
                                {productos.data?.map((producto) => <ProductosAdminRow key={producto.id} producto={producto} />)}
                            </tbody>
                        </table>
                    </div>

                    {/* Paginación con datos del backend */}
                    <div className="mt-4 flex justify-center">
                        {productos.links && (
                            <div className="flex items-center">
                                {productos.links.map((link, index) => (
                                    <button
                                        key={index}
                                        onClick={() => link.url && handlePageChange(link.url.split('page=')[1])}
                                        disabled={!link.url}
                                        className={`px-4 py-2 ${
                                            link.active
                                                ? 'bg-primary-orange text-white'
                                                : link.url
                                                  ? 'bg-gray-300 text-black'
                                                  : 'bg-gray-200 text-gray-500 opacity-50'
                                        } ${index === 0 ? 'rounded-l-md' : ''} ${index === productos.links.length - 1 ? 'rounded-r-md' : ''}`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Información de paginación */}
                    <div className="mt-2 text-center text-sm text-gray-600">
                        Mostrando {productos.from || 0} a {productos.to || 0} de {productos.total} resultados
                    </div>
                </div>
            </div>
        </Dashboard>
    );
}
