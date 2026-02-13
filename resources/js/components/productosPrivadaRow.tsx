import { faChevronDown, faChevronUp } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { router, useForm, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

/**
 * Modal simple (Tailwind)
 */
function CartModal({ open, message, onClose }) {
    if (!open) return null;

    return (
        <div className="fixed inset-0 z-[9999] flex items-center justify-center">
            {/* Overlay */}
            <div className="absolute inset-0 bg-black/50" onClick={onClose} />

            {/* Caja */}
            <div className="relative z-10 w-[92%] max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 className="mb-2 text-lg font-semibold text-gray-900">Carrito</h3>
                <p className="text-sm text-gray-700">{message}</p>

                <div className="mt-5 flex justify-end gap-2">
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-md bg-black px-4 py-2 text-sm font-medium text-white hover:opacity-90"
                    >
                        OK
                    </button>
                </div>
            </div>
        </div>
    );
}

export default function ProductosPrivadaRow({ producto, margenSwitch, margen }) {
    const { auth, ziggy, margenes } = usePage().props;
    const { user } = auth;

    const [cantidad, setCantidad] = useState(producto?.qty != 1 ? producto?.qty : producto?.unidad_pack);

    // ✅ Modal state
    const [modalOpen, setModalOpen] = useState(false);
    const [modalMsg, setModalMsg] = useState('');

    const openModal = (msg) => {
        setModalMsg(msg);
        setModalOpen(true);

        // opcional: autocerrar a los 1.4s
        window.clearTimeout(window.__cartModalTimer);
        window.__cartModalTimer = window.setTimeout(() => {
            setModalOpen(false);
        }, 1400);
    };

    useEffect(() => {
        if (producto?.rowId) {
            router.post(
                route('update'),
                {
                    qty: cantidad,
                    rowId: producto?.rowId,
                },
                {
                    preserveScroll: true,
                },
            );
        }
    }, [cantidad]);

    const { post, setData } = useForm({
        id: producto?.id,
        name: producto?.code,
        qty: cantidad,
        price: producto?.precio,
        rowId: producto?.rowId,
    });

    useEffect(() => {
        setData({
            id: producto?.id,
            name: producto?.code,
            qty: cantidad,
            price: producto?.precio,
            rowId: producto?.rowId,
        });
    }, [cantidad, producto]);

    const addToCart = (e) => {
        e.preventDefault();

        post(route('addtocart'), {
            preserveScroll: true,
            onSuccess: () => {
                openModal('Producto agregado al carrito ✅');
            },
            onError: (errors) => {
                openModal('Error al agregar producto al carrito ❌');
                console.log(errors);
            },
        });
    };

    const removeFromCart = (e) => {
        e.preventDefault();

        post(route('remove'), {
            preserveScroll: true,
            onSuccess: () => {
                openModal('Producto eliminado del carrito ✅');
            },
            onError: (errors) => {
                openModal('Error al eliminar producto del carrito ❌');
                console.log(errors);
            },
        });
    };

    return (
        <>
            {/* ✅ Modal */}
            <CartModal open={modalOpen} message={modalMsg} onClose={() => setModalOpen(false)} />

            {/* Vista desktop - tabla */}
            <div className="grid h-fit grid-cols-9 items-center border-b border-gray-200 py-2 text-[15px] text-black max-sm:hidden">
                <div className="h-[80px] w-[80px] rounded-sm border">
                    <img src={producto?.imagen_final} className="h-full w-full rounded-sm object-cover" alt="" />
                </div>

                <p className="">{producto?.code}</p>
                <p className="">{producto?.marca?.name}</p>
                <p className="">{producto?.categoria?.name}</p>

                {margenSwitch ? (
                    <div className="relative">
                        <p className="text-center">
                            ${' '}
                            {(
                                Number(producto?.precio) *
                                (1 + Number(margenes?.general ?? 0) / 100) *
                                (1 + Number(margenes?.tipos[producto?.categoria?.name] ?? 0) / 100) *
                                cantidad
                            )?.toLocaleString('es-AR', {
                                maximumFractionDigits: 2,
                                minimumFractionDigits: 2,
                            })}
                        </p>
                        <p className="absolute w-full text-center text-gray-400 line-through">
                            ${' '}
                            {Number(producto?.precio * cantidad)?.toLocaleString('es-AR', {
                                maximumFractionDigits: 2,
                                minimumFractionDigits: 2,
                            })}
                        </p>
                    </div>
                ) : (
                    <div className="relative">
                        <p className="text-center">
                            $ {Number(producto?.precio)?.toLocaleString('es-AR', { maximumFractionDigits: 2, minimumFractionDigits: 2 })}
                        </p>
                    </div>
                )}

                <p className="text-center text-green-500">
                    {[user?.descuento_uno, user?.descuento_dos, user?.descuento_tres]
                        .filter(Boolean)
                        .map((descuento) => descuento + '%')
                        .join(' + ')}
                </p>

                {margenSwitch ? (
                    <div className="relative">
                        <p className="text-center">
                            ${' '}
                            {(
                                Number(producto?.precio) *
                                cantidad *
                                (1 + Number(margenes?.general ?? 0) / 100) *
                                (1 + Number(margenes?.tipos[producto?.categoria?.name] ?? 0) / 100) *
                                (1 - Number(user?.descuento_uno) / 100) *
                                (1 - Number(user?.descuento_dos) / 100) *
                                (1 - Number(user?.descuento_tres) / 100)
                            ).toLocaleString('es-AR', {
                                maximumFractionDigits: 2,
                                minimumFractionDigits: 2,
                            })}
                        </p>
                        <p className="absolute w-full text-center text-gray-400 line-through">
                            ${' '}
                            {(
                                Number(producto?.precio) *
                                (1 + Number(margenes?.general ?? 0) / 100) *
                                (1 + Number(margenes?.tipos[producto?.categoria?.name] ?? 0) / 100) *
                                (1 - Number(user?.descuento_uno) / 100) *
                                (1 - Number(user?.descuento_dos) / 100) *
                                (1 - Number(user?.descuento_tres) / 100) *
                                cantidad
                            )?.toLocaleString('es-AR', {
                                maximumFractionDigits: 2,
                                minimumFractionDigits: 2,
                            })}
                        </p>
                    </div>
                ) : (
                    <p className="text-center">
                        ${' '}
                        {user?.descuento_uno &&
                            (
                                Number(producto?.precio) *
                                cantidad *
                                (1 - Number(user?.descuento_uno) / 100) *
                                (1 - Number(user?.descuento_dos) / 100) *
                                (1 - Number(user?.descuento_tres) / 100)
                            ).toLocaleString('es-AR', {
                                maximumFractionDigits: 2,
                                minimumFractionDigits: 2,
                            })}
                    </p>
                )}

                <p className="flex justify-end">
                    <div className="flex h-[38px] w-[99px] flex-row items-center border border-[#EEEEEE] px-2">
                        <input value={cantidad} type="text" className="h-full w-full focus:outline-none" readOnly />
                        <div className="flex h-full flex-col justify-center">
                            <button onClick={() => setCantidad(Number(cantidad) + Number(producto?.unidad_pack))} className="flex items-center">
                                <FontAwesomeIcon icon={faChevronUp} size="xs" />
                            </button>
                            <button
                                onClick={() =>
                                    setCantidad(
                                        Number(cantidad) > producto?.unidad_pack ? Number(cantidad) - Number(producto?.unidad_pack) : Number(cantidad),
                                    )
                                }
                                className="flex items-center"
                            >
                                <FontAwesomeIcon icon={faChevronDown} size="xs" />
                            </button>
                        </div>
                    </div>
                </p>

                <p className="flex justify-center">
                    {ziggy.location.includes('carrito') ? (
                        <button
                            type="button"
                            onClick={removeFromCart}
                            className="border-primary-orange flex h-[36px] w-[36px] items-center justify-center rounded-sm border transition duration-300 hover:scale-95 hover:shadow-sm"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path
                                    d="M2 4H14M12.6667 4V13.3333C12.6667 14 12 14.6667 11.3333 14.6667H4.66667C4 14.6667 3.33333 14 3.33333 13.3333V4M5.33333 4V2.66667C5.33333 2 6 1.33334 6.66667 1.33334H9.33333C10 1.33334 10.6667 2 10.6667 2.66667V4M6.66667 7.33334V11.3333M9.33333 7.33334V11.3333"
                                    stroke="#FF120B"
                                    strokeWidth="1.5"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                />
                            </svg>
                        </button>
                    ) : (
                        <button
                            onClick={addToCart}
                            className="border-primary-orange flex h-[36px] w-[36px] items-center justify-center rounded-sm border transition duration-300 hover:scale-95 hover:shadow-sm"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <g clipPath="url(#clip0_11505_945)">
                                    <path
                                        d="M1.3667 1.36667H2.70003L4.47337 9.64667C4.53842 9.94991 4.70715 10.221 4.95051 10.4132C5.19387 10.6055 5.49664 10.7069 5.8067 10.7H12.3267C12.6301 10.6995 12.9244 10.5955 13.1607 10.4052C13.3971 10.2149 13.5615 9.94969 13.6267 9.65334L14.7267 4.7H3.41337M6.00003 14C6.00003 14.3682 5.70156 14.6667 5.33337 14.6667C4.96518 14.6667 4.6667 14.3682 4.6667 14C4.6667 13.6318 4.96518 13.3333 5.33337 13.3333C5.70156 13.3333 6.00003 13.6318 6.00003 14ZM13.3334 14C13.3334 14.3682 13.0349 14.6667 12.6667 14.6667C12.2985 14.6667 12 14.3682 12 14C12 13.6318 12.2985 13.3333 12.6667 13.3333C13.0349 13.3333 13.3334 13.6318 13.3334 14Z"
                                        stroke="#FF120B"
                                        strokeWidth="1.5"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                    />
                                </g>
                                <defs>
                                    <clipPath id="clip0_11505_945">
                                        <rect width="16" height="16" fill="white" />
                                    </clipPath>
                                </defs>
                            </svg>
                        </button>
                    )}
                </p>
            </div>

            {/* Vista móvil - tarjeta */}
            <div className="mb-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:hidden">
                <div className="mb-3 flex items-start gap-3">
                    <div className="h-[60px] w-[60px] flex-shrink-0 rounded-sm border">
                        <img src={producto?.imagen_final} className="h-full w-full object-contain" alt="" />
                    </div>
                    <div className="min-w-0 flex-1">
                        <div className="mb-1 flex items-center gap-2">
                            <p className="text-sm font-medium text-gray-900">Cod. Ori: {producto?.code}</p>
                            <p className="text-primary-orange text-sm font-medium">Cod. SR: {producto?.code_sr}</p>
                        </div>
                        <p className="line-clamp-2 text-sm text-gray-600">{producto?.name}</p>
                        {producto?.code_oem && (
                            <div className="grid grid-cols-2">
                                <p className="text-xs text-gray-500">Codigo OEM:</p>
                                <div className="flex flex-col gap-1">
                                    {producto?.code_oem?.split('/').map((item) => (
                                        <span key={item} className="block text-xs text-gray-500">
                                            {item}
                                        </span>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                <div className="mb-3">
                    {margenSwitch ? (
                        <div className="relative">
                            <p className="text-center">
                                ${' '}
                                {(
                                    Number(Number(producto)) *
                                    (1 + Number(margenes?.general ?? 0) / 100) *
                                    (1 + Number(margenes?.tipos[producto?.categoria?.name] ?? 0) / 100) *
                                    cantidad
                                )?.toLocaleString('es-AR', {
                                    maximumFractionDigits: 2,
                                    minimumFractionDigits: 2,
                                })}
                            </p>
                            <p className="absolute top-0 -left-20 w-full text-center text-gray-400 line-through">
                                ${' '}
                                {Number(Number(producto?.precio) * cantidad)?.toLocaleString('es-AR', {
                                    maximumFractionDigits: 2,
                                    minimumFractionDigits: 2,
                                })}
                            </p>
                        </div>
                    ) : (
                        <div className="relative">
                            <p className="text-center">
                                ${' '}
                                {Number(producto?.precio?.precio * cantidad)?.toLocaleString('es-AR', {
                                    maximumFractionDigits: 2,
                                    minimumFractionDigits: 2,
                                })}
                            </p>
                        </div>
                    )}
                </div>

                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="flex h-[32px] w-[80px] flex-row items-center rounded border border-[#EEEEEE] px-2">
                            <input value={cantidad} type="text" className="h-full w-full text-center text-sm focus:outline-none" readOnly />
                            <div className="flex h-full flex-col justify-center">
                                <button onClick={() => setCantidad(Number(cantidad) + Number(producto?.unidad_pack))} className="flex items-center">
                                    <FontAwesomeIcon icon={faChevronUp} size="xs" />
                                </button>
                                <button
                                    onClick={() =>
                                        setCantidad(
                                            Number(cantidad) > producto?.unidad_pack
                                                ? Number(cantidad) - Number(producto?.unidad_pack)
                                                : Number(cantidad),
                                        )
                                    }
                                    className="flex items-center"
                                >
                                    <FontAwesomeIcon icon={faChevronDown} size="xs" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        {ziggy.location.includes('carrito') ? (
                            <button type="button" onClick={removeFromCart} className="rounded p-2 text-red-500 hover:bg-red-50">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="20"
                                    height="20"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="2"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                >
                                    <path d="M3 6h18" />
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                </svg>
                            </button>
                        ) : (
                            <button onClick={addToCart} className="rounded p-2 text-blue-600 hover:bg-blue-50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <g clipPath="url(#clip0_11505_945)">
                                        <path
                                            d="M1.3667 1.36667H2.70003L4.47337 9.64667C4.53842 9.94991 4.70715 10.221 4.95051 10.4132C5.19387 10.6055 5.49664 10.7069 5.8067 10.7H12.3267C12.6301 10.6995 12.9244 10.5955 13.1607 10.4052C13.3971 10.2149 13.5615 9.94969 13.6267 9.65334L14.7267 4.7H3.41337M6.00003 14C6.00003 14.3682 5.70156 14.6667 5.33337 14.6667C4.96518 14.6667 4.6667 14.3682 4.6667 14C4.6667 13.6318 4.96518 13.3333 5.33337 13.3333C5.70156 13.3333 6.00003 13.6318 6.00003 14ZM13.3334 14C13.3334 14.3682 13.0349 14.6667 12.6667 14.6667C12.2985 14.6667 12 14.3682 12 14C12 13.6318 12.2985 13.3333 12.6667 13.3333C13.0349 13.3333 13.3334 13.6318 13.3334 14Z"
                                            stroke="#FF120B"
                                            strokeWidth="1.5"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                        />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_11505_945">
                                            <rect width="16" height="16" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </button>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
