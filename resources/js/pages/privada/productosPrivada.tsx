import ProductosPrivadaRow from '@/components/productosPrivadaRow';
import SearchBar from '@/components/searchBar';
import Slider from '@/components/slider';
import { Head, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import toast from 'react-hot-toast';
import DefaultLayout from '../defaultLayout';
import axios from 'axios';


export default function ProductosPrivada({ categorias, subcategorias }) {
    const { productos, auth, clienteSeleccionado } = usePage().props;
    const user = auth.user;

    const [margenSwitch, setMargenSwitch] = useState(false);
    const [vendedorScreen, setVendedorScreen] = useState(user?.rol == 'vendedor' && clienteSeleccionado == null);
    const [selectedUserId, setSelectedUserId] = useState(null);

    const [marcaSelected, setMarcaSelected] = useState();

    useEffect(() => {
        if (user?.rol === 'vendedor' && !clienteSeleccionado) {
            setVendedorScreen(true);
        } else {
            setVendedorScreen(false);
        }
    }, [user, clienteSeleccionado]);

    useEffect(() => {
        localStorage.setItem('margenSwitch', JSON.stringify(margenSwitch));
    }, [margenSwitch]);

    const handlePageChange = (page) => {
        router.get(
            route('index.privada.productos'),
            {
                page: page,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleFastBuy = async (e) => {
  e.preventDefault();

  try {
    const { data } = await axios.post(route('compraRapida'), {
      code: e.target.code.value,
      qty: Number(e.target.qty.value),
    });

    if (data.ok) {
      toast.success(data.message || 'Producto añadido al carrito');
    } else {
      toast.error(data.message || 'Error al añadir el producto');
    }
  } catch (error) {
    console.error(error);
    toast.error('Error al conectar con el servidor');
  }
};


    const seleccionarCliente = (e) => {
        e.preventDefault();

        router.post(
            route('seleccionarCliente'),
            { cliente_id: selectedUserId },
            {
                onSuccess: () => {
                    setVendedorScreen(false);
                    toast.success('Cliente seleccionado correctamente');
                },
            },
        );
    };

    return (
        <DefaultLayout>
            <Head>
                <title>Productos</title>
            </Head>
            <Slider />
            {vendedorScreen && (
                <div className="fixed z-[100] flex h-screen w-screen items-center justify-center bg-black/50">
                    <div className="flex h-[218px] w-[476px] items-center justify-center bg-white max-sm:h-auto max-sm:w-[90%] max-sm:p-4">
                        <form onSubmit={seleccionarCliente} className="flex w-[350px] flex-col items-center gap-6 max-sm:w-full">
                            <h2 className="text-[16px] font-semibold max-sm:text-[14px]">Seleccionar cliente</h2>
                            <select
                                onChange={(e) => setSelectedUserId(e.target.value)}
                                className="h-[48px] w-full border px-2 max-sm:h-[40px]"
                                name="cliente_id"
                                id=""
                            >
                                <option value="">Seleccione un cliente</option>
                                {user?.clientes?.map((cliente) => (
                                    <option key={cliente.id} value={cliente.id}>
                                        {cliente.name}
                                    </option>
                                ))}
                            </select>
                            <div className="flex w-full justify-between gap-5 text-[16px] max-sm:gap-3 max-sm:text-[14px]">
                                <button
                                    type="button"
                                    onClick={() => setVendedorScreen(false)}
                                    className="text-primary-orange border-primary-orange hover:bg-primary-orange h-[41px] w-full border transition duration-300 hover:text-white max-sm:h-[36px]"
                                >
                                    Cancelar
                                </button>
                                <button
                                    onClick={seleccionarCliente}
                                    className="bg-primary-orange hover:bg-opacity-80 hover:text-primary-orange hover:border-primary-orange h-[41px] w-full text-white transition duration-300 hover:border hover:bg-transparent max-sm:h-[36px]"
                                >
                                    Confirmar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            <div className="mb-10 flex flex-col max-sm:gap-6">
                <div className="bg-primary-orange mt-10 h-[123px] w-full max-sm:h-auto max-sm:py-4">
                    <div className="mx-auto flex h-full w-[1200px] flex-row items-center max-sm:w-full max-sm:flex-col max-sm:gap-4 max-sm:px-4">
                        <p className="w-1/3 text-[24px] font-medium text-white max-sm:w-full max-sm:text-center max-sm:text-[20px]">Compra rápida</p>
                        <form
                            onSubmit={handleFastBuy}
                            className="grid h-[47px] w-full grid-cols-5 gap-5 max-sm:h-auto max-sm:grid-cols-1 max-sm:gap-3"
                        >
                            <input
                                name="code"
                                placeholder="Codigo"
                                type="text"
                                className="focus:outline-primary-orange col-span-2 rounded-sm bg-white pl-2 transition duration-300 outline-none placeholder:text-black max-sm:col-span-1 max-sm:h-[40px]"
                            />
                            <input
                                name="qty"
                                placeholder="Cantidad"
                                type="number"
                                className="focus:outline-primary-orange col-span-2 rounded-sm bg-white pl-2 transition duration-300 outline-none placeholder:text-black max-sm:col-span-1 max-sm:h-[40px]"
                            />
                            <button className="text-primary-orange rounded-sm bg-white font-bold transition duration-300 hover:border hover:border-white hover:bg-transparent hover:text-white max-sm:h-[40px]">
                                Añadir
                            </button>
                        </form>
                    </div>
                </div>
                <SearchBar />
                <div className="mx-auto mt-10 flex w-[1200px] flex-col gap-2 max-sm:w-full max-sm:px-4">
                    <div className="w-full">
                        <div className="grid h-[52px] grid-cols-9 items-center rounded-t-sm bg-black text-white max-sm:hidden max-sm:h-[40px] max-sm:grid-cols-4 max-sm:text-[12px]">
                            <p className="max-sm:hidden"></p>
                            <p className="max-sm:hidden">Código</p>
                            <p>Marca</p>
                            <p className="">Categoria</p>
                            <p className="text-center max-sm:hidden">Precio</p>
                            <p className="text-center max-sm:hidden">Descuentos</p>
                            <p className="text-center max-sm:hidden">Precio con descuento</p>
                            <p className="text-center max-sm:hidden">Cantidad</p>
                            <p className="text-center max-sm:hidden"></p>
                            <p></p>
                        </div>
                        {productos?.data?.map((producto, index) => (
                            <ProductosPrivadaRow
                                key={producto?.id}
                                producto={producto}
                                margenSwitch={margenSwitch}
                                margen={localStorage.getItem('margen') || 0}
                            />
                        ))}
                    </div>
                    <div className="mt-4 flex justify-center">
                        {productos.links && (
                            <div className="flex items-center max-sm:flex-wrap max-sm:gap-1">
                                {productos.links.map((link, index) => (
                                    <button
                                        key={index}
                                        onClick={() => link.url && handlePageChange(link.url.split('page=')[1])}
                                        disabled={!link.url}
                                        className={`px-4 py-2 max-sm:px-2 max-sm:py-1 max-sm:text-[12px] ${
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
                    <div className="mt-2 text-center text-sm text-gray-600 max-sm:text-[12px]">
                        Mostrando {productos.from || 0} a {productos.to || 0} de {productos.total} resultados
                    </div>
                </div>
            </div>
        </DefaultLayout>
    );
}
