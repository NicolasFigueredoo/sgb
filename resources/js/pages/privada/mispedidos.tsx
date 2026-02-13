import MispedidosRow from '@/components/mispedidosRow';
import { Head, usePage } from '@inertiajs/react';
import DefaultLayout from '../defaultLayout';

export default function Mispedidos() {
    const { pedidos } = usePage().props;

    return (
        <DefaultLayout>
            <Head>
                <title>Pedidos</title>
            </Head>
            <div className="mx-auto min-h-[40vh] max-w-[1200px] py-20 max-sm:p-10">
                <div className="col-span-2 grid w-full items-start">
                    <div className="w-full">
                        <div className="bg-black grid h-[52px] grid-cols-7 items-center rounded-t-sm text-white max-sm:hidden">
                            <p></p>
                            <p>Nº de pedido</p>
                            <p>Fecha de pedido</p>
                            <p>Estado</p>
                            <p>Importe</p>
                            <p></p>
                            <p></p>
                        </div>
                        {pedidos?.map((pedido) => <MispedidosRow key={pedido?.id} pedido={pedido} />)}
                    </div>
                </div>
            </div>
        </DefaultLayout>
    );
}
