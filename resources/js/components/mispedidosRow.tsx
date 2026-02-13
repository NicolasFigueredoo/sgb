import { router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import toast from 'react-hot-toast';

export default function MispedidosRow({ pedido }) {
    const [detalleView, setDetalleView] = useState(false);

    // ✅ Evita crash si no viene productos o no es array
    const productos = useMemo(() => {
        return Array.isArray(pedido?.productos) ? pedido.productos : [];
    }, [pedido]);

    const recomprar = () => {
        router.post(
            route('recomprar'),
            { pedido_id: pedido?.id },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success('Productos añadidos al carrito');
                },
                // ✅ Inertia onError devuelve un objeto de errors, no axios error.response
                onError: () => {
                    toast.error('Error al añadir productos al carrito');
                },
            },
        );
    };

    const fechaFormateada = (iso) => {
  if (!iso) return '-';
  const d = new Date(iso);
  return d.toLocaleString('es-AR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

    return (
        <>
            {/* MODAL */}
            {detalleView && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                    onClick={() => setDetalleView(false)}
                >
                    {/* ✅ Stop propagation: click adentro NO cierra */}
                    <div
                        className="relative mx-auto max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-lg bg-white shadow-xl"
                        onClick={(e) => e.stopPropagation()}
                    >
                        {/* Header */}
                        <div className="flex items-center justify-between border-b border-gray-200 p-4 md:p-6">
                            <h2 className="text-lg font-semibold text-gray-900 md:text-xl">
                                Detalles del Pedido #{pedido?.id}
                            </h2>

                            <button
                                type="button"
                                onClick={() => setDetalleView(false)}
                                className="text-2xl font-bold text-gray-400 hover:text-gray-600"
                            >
                                ×
                            </button>
                        </div>

                        {/* Content */}
                        <div className="p-4 md:p-6">
                            <div className="mb-6 grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-2">
                                {/* Información del Pedido */}
                                <div className="rounded-lg bg-gray-50 p-3 md:p-4">
                                    <h3 className="mb-3 text-sm font-semibold text-gray-900 md:text-base">
                                        Información del Pedido
                                    </h3>

                                    <div className="space-y-2">
                                        <div className="flex flex-col gap-1 md:flex-row md:justify-between">
                                            <span className="text-sm text-gray-600 md:text-base">Tipo de Entrega:</span>
                                            <span className="text-sm font-medium md:text-base">{pedido?.tipo_entrega ?? '-'}</span>
                                        </div>

                                        {!!pedido?.mensaje && (
                                            <div className="pt-2">
                                                <span className="text-sm text-gray-600 md:text-base">Mensaje:</span>
                                                <p className="mt-1 rounded border bg-white p-2 text-xs text-gray-800 md:text-sm">
                                                    {pedido.mensaje}
                                                </p>
                                            </div>
                                        )}
                                    </div>
                                </div>

                                {/* Resumen de Costos */}
                                <div className="rounded-lg bg-gray-50 p-3 md:p-4">
                                    <h3 className="mb-3 text-sm font-semibold text-gray-900 md:text-base">
                                        Resumen de Costos
                                    </h3>

                                    <div className="space-y-2">
                                        <div className="flex justify-between">
                                            <span className="text-sm text-gray-600 md:text-base">Subtotal:</span>
                                            <span className="text-sm font-medium md:text-base">
                                                ${' '}
                                                {Number(pedido?.subtotal ?? 0).toLocaleString('es-AR', {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2,
                                                })}
                                            </span>
                                        </div>

                                        <div className="flex justify-between">
                                            <span className="text-sm text-gray-600 md:text-base">Descuento:</span>
                                            <span className="text-sm font-medium text-green-600 md:text-base">
                                                -${' '}
                                                {Number(pedido?.descuento ?? 0).toLocaleString('es-AR', {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2,
                                                })}
                                            </span>
                                        </div>

                                        <div className="flex justify-between">
                                            <span className="text-sm text-gray-600 md:text-base">IVA:</span>
                                            <span className="text-sm font-medium md:text-base">
                                                ${' '}
                                                {Number(pedido?.iva ?? 0).toLocaleString('es-AR', {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2,
                                                })}
                                            </span>
                                        </div>

                                        <div className="border-t pt-2">
                                            <div className="flex justify-between">
                                                <span className="text-base font-semibold text-gray-900 md:text-lg">Total:</span>
                                                <span className="text-base font-semibold text-gray-900 md:text-lg">
                                                    ${' '}
                                                    {Number(pedido?.total ?? 0).toLocaleString('es-AR', {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2,
                                                    })}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Productos */}
                            <div>
                                <h3 className="mb-3 text-sm font-semibold text-gray-900 md:text-base">Productos</h3>

                                {/* ✅ Si no hay productos, no revienta */}
                                {productos.length === 0 ? (
                                    <div className="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-600">
                                        Este pedido no tiene productos cargados (o no vinieron en el payload).
                                    </div>
                                ) : (
                                    <>
                                        {/* Vista móvil - Cards */}
                                        <div className="space-y-3 md:hidden">
                                            {productos.map((p, index) => (
                                                <div key={index} className="rounded-lg border border-gray-200 bg-white p-3">
                                                    <div className="space-y-2">
                                                        <div className="flex justify-between">
                                                            <span className="text-xs text-gray-500">Código:</span>
                                                            <span className="text-sm font-medium">{p?.producto?.code ?? '-'}</span>
                                                        </div>

                                                        <div className="flex flex-col">
                                                            <span className="text-xs text-gray-500">Producto:</span>
                                                            <span className="text-sm font-medium">{p?.producto?.name ?? '-'}</span>
                                                        </div>

                                                        <div className="grid grid-cols-3 gap-2">
                                                            <div className="text-center">
                                                                <span className="block text-xs text-gray-500">Cantidad</span>
                                                                <span className="text-sm font-medium">{p?.cantidad ?? 0}</span>
                                                            </div>
                                                            <div className="text-center">
                                                                <span className="block text-xs text-gray-500">Precio</span>
                                                                <span className="text-sm font-medium">
                                                                    ${' '}
                                                                    {Number(p?.precio_unitario ?? 0).toLocaleString('es-AR', {
                                                                        minimumFractionDigits: 2,
                                                                        maximumFractionDigits: 2,
                                                                    })}
                                                                </span>
                                                            </div>
                                                            <div className="text-center">
                                                                <span className="block text-xs text-gray-500">Subtotal</span>
                                                                <span className="text-sm font-semibold">
                                                                    ${' '}
                                                                    {Number((p?.cantidad ?? 0) * (p?.precio_unitario ?? 0)).toLocaleString('es-AR', {
                                                                        minimumFractionDigits: 2,
                                                                        maximumFractionDigits: 2,
                                                                    })}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>

                                        {/* Vista desktop - Tabla */}
                                        <div className="hidden overflow-x-auto md:block">
                                            <table className="min-w-full rounded-lg border border-gray-200 bg-white">
                                                <thead className="bg-gray-50">
                                                    <tr>
                                                        <th className="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Código</th>
                                                        <th className="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Nombre</th>
                                                        <th className="px-4 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Cantidad</th>
                                                        <th className="px-4 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">Precio</th>
                                                        <th className="px-4 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">Subtotal</th>
                                                    </tr>
                                                </thead>

                                                <tbody className="divide-y divide-gray-200">
                                                    {productos.map((p, index) => (
                                                        <tr key={index} className="hover:bg-gray-50">
                                                            <td className="px-4 py-3 text-sm text-gray-900">{p?.producto?.code ?? '-'}</td>
                                                            <td className="px-4 py-3 text-sm text-gray-900">{p?.producto?.name ?? '-'}</td>
                                                            <td className="px-4 py-3 text-center text-sm text-gray-900">{p?.cantidad ?? 0}</td>
                                                            <td className="px-4 py-3 text-right text-sm text-gray-900">
                                                                ${' '}
                                                                {Number(p?.precio_unitario ?? 0).toLocaleString('es-AR', {
                                                                    minimumFractionDigits: 2,
                                                                    maximumFractionDigits: 2,
                                                                })}
                                                            </td>
                                                            <td className="px-4 py-3 text-right text-sm font-medium text-gray-900">
                                                                ${' '}
                                                                {Number((p?.cantidad ?? 0) * (p?.precio_unitario ?? 0)).toLocaleString('es-AR', {
                                                                    minimumFractionDigits: 2,
                                                                    maximumFractionDigits: 2,
                                                                })}
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>
                                    </>
                                )}
                            </div>
                        </div>

                        {/* Footer */}
                        <div className="flex justify-end border-t border-gray-200 p-4 md:p-6">
                            <button
                                onClick={() => setDetalleView(false)}
                                className="bg-primary-orange hover:bg-primary-orange/80 rounded-md px-4 py-2 text-sm text-white transition-colors md:text-base"
                            >
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Vista desktop - Grid */}
            <div className="hidden grid-cols-7 gap-2 border-b py-2 md:grid">
                <div className="flex items-center">
                    <div className="flex h-[80px] w-[80px] items-center justify-center bg-[#F5F5F5]">
                        {/* Icono */}
                        <svg xmlns="http://www.w3.org/2000/svg" width="43" height="43" viewBox="0 0 43 43" fill="none">
                            <path d="M10.7497 39.4167V7.16667C10.7497 6.21631 11.1272 5.30488 11.7992 4.63287C12.4712 3.96086 13.3826 3.58334 14.333 3.58334H28.6663C29.6167 3.58334 30.5281 3.96086 31.2001 4.63287C31.8721 5.30488 32.2497 6.21631 32.2497 7.16667V39.4167M10.7497 39.4167H32.2497M10.7497 39.4167H7.16634C6.21598 39.4167 5.30455 39.0391 4.63254 38.3671C3.96054 37.6951 3.58301 36.7837 3.58301 35.8333V25.0833C3.58301 24.133 3.96054 23.2215 4.63254 22.5495C5.30455 21.8775 6.21598 21.5 7.16634 21.5H10.7497M32.2497 39.4167H35.833C36.7834 39.4167 37.6948 39.0391 38.3668 38.3671C39.0388 37.6951 39.4163 36.7837 39.4163 35.8333V19.7083C39.4163 18.758 39.0388 17.8465 38.3668 17.1745C37.6948 16.5025 36.7834 16.125 35.833 16.125H32.2497M17.9163 10.75H25.083M17.9163 17.9167H25.083M17.9163 25.0833H25.083M17.9163 32.25H25.083" stroke="#FF120B" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"/>
                        </svg>
                    </div>
                </div>

                <div className="flex items-center">{pedido?.id}</div>
                <div className="flex items-center">{pedido.fecha}</div>
                <div className="flex items-center font-bold">{pedido?.estado}</div>
                <div className="flex items-center">
                    $ {Number(pedido?.total ?? 0).toLocaleString('es-AR', { maximumFractionDigits: 2, minimumFractionDigits: 2 })}
                </div>

                <div className="flex items-center">
                    <button
                        type="button"
                        onClick={() => setDetalleView(true)}
                        className="bg-primary-orange h-10 w-full min-w-[138px] rounded-sm font-bold text-white"
                    >
                        Ver detalles
                    </button>
                </div>

                <div className="flex items-center">
                    <button
                        type="button"
                        onClick={recomprar}
                        className="border-primary-orange text-primary-orange h-10 w-full min-w-[138px] rounded-sm border font-medium"
                    >
                        Recomprar
                    </button>
                </div>
            </div>

            {/* Vista móvil - Card */}
            <div className="border-b border-gray-200 bg-white p-4 md:hidden">
                <div className="flex items-start gap-4">
                    <div className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded bg-[#F5F5F5]">
                        {/* Icono */}
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 43 43" fill="none">
                            <path
                                d="M10.7497 39.4167V7.16668C10.7497 6.21632 11.1272 5.30488 11.7992 4.63288C12.4712 3.96087 13.3826 3.58334 14.333 3.58334H28.6663C29.6167 3.58334 30.5281 3.96087 31.2001 4.63288C31.8721 5.30488 32.2497 6.21632 32.2497 7.16668V39.4167M10.7497 39.4167H32.2497"
                                stroke="#0072C6"
                                strokeWidth="3"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                            />
                        </svg>
                    </div>

                    <div className="flex-1 space-y-2">
                        <div className="flex items-center justify-between">
                            <h3 className="text-sm font-semibold text-gray-900">Pedido #{pedido?.id}</h3>
                            <span className="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">{pedido?.estado}</span>
                        </div>

                        <div className="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span className="text-gray-500">Fecha:</span>
                                <p className="font-medium">{pedido?.created_at}</p>
                            </div>
                            <div>
                                <span className="text-gray-500">Total:</span>
                                <p className="font-semibold">
                                    $ {Number(pedido?.total ?? 0).toLocaleString('es-AR', { maximumFractionDigits: 2, minimumFractionDigits: 2 })}
                                </p>
                            </div>
                        </div>

                        <div className="flex gap-2 pt-2">
                            <button
                                type="button"
                                onClick={() => setDetalleView(true)}
                                className="bg-primary-orange flex-1 rounded-sm py-2 text-sm font-bold text-white"
                            >
                                Ver detalles
                            </button>

                            <button
                                type="button"
                                onClick={recomprar}
                                className="border-primary-orange text-primary-orange flex-1 rounded-sm border py-2 text-sm font-medium"
                            >
                                Recomprar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
