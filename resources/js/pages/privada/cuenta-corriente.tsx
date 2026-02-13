import CuentaCorrienteRow from '@/components/CuentaCorrienteRow';
import { Head, usePage } from '@inertiajs/react';
import DefaultLayout from '../defaultLayout';

export default function CuentaCorriente() {
    const { movimientos, saldo, cliente, mensaje } = usePage().props;

    const formatMoney = (amount) => {
        return new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS'
        }).format(amount);
    };

    return (
        <DefaultLayout>
            <Head>
                <title>Cuenta Corriente</title>
            </Head>
            
            <div className="mx-auto min-h-[40vh] max-w-[1200px] py-20 max-sm:p-10">
               

                {mensaje ? (
                    <div className="rounded-lg bg-yellow-50 p-6 border border-yellow-200">
                        <p className="text-yellow-800">{mensaje}</p>
                    </div>
                ) : (
                    <>
                  

                        {/* Tabla de movimientos */}
                        <div className="col-span-2 grid w-full items-start">
                            <div className="w-full">
                                {/* Header de la tabla */}
                                <div className="bg-black grid h-[52px] grid-cols-5 items-center rounded-t-sm text-white max-sm:hidden">
                                    <p className="text-center"></p>
                                    <p className="text-center">Fecha</p>
                                    <p className="text-center">N° Comprobante</p>
                                    <p className="text-center">Descripción</p>
                     
                                    <p className="text-center">Importe</p>
                                </div>

                                {/* Filas de movimientos */}
                                {movimientos.length > 0 ? (
                                    movimientos.map((movimiento) => (
                                        <CuentaCorrienteRow 
                                            key={movimiento.id} 
                                            movimiento={movimiento} 
                                        />
                                    ))
                                ) : (
                                    <div className="bg-white p-12 text-center text-gray-500 border border-gray-200 rounded-b-sm">
                                        No hay movimientos en cuenta corriente
                                    </div>
                                )}
                            </div>
                        </div>
                    </>
                )}
            </div>
        </DefaultLayout>
    );
}