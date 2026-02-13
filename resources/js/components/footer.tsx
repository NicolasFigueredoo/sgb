import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

const Footer = () => {
    const { logos, contacto } = usePage().props;

    const [formData, setFormData] = useState({ email: '' });
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [showSuccess, setShowSuccess] = useState(false);
    const [showError, setShowError] = useState(false);
    const [errorMessage, setErrorMessage] = useState('');

    const hideMessages = () => {
        setShowSuccess(false);
        setShowError(false);
    };

    const showSuccessMessage = () => {
        hideMessages();
        setShowSuccess(true);
        setTimeout(() => hideMessages(), 5000);
    };

    const showErrorMessage = (message) => {
        hideMessages();
        setErrorMessage(message);
        setShowError(true);
        setTimeout(() => hideMessages(), 5000);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setIsSubmitting(true);

        try {
            await router.post('/newsletter', formData, {
                onSuccess: () => {
                    showSuccessMessage();
                    setFormData({ email: '' });
                },
                onError: (errors) => {
                    const errorMsg = errors.email ? errors.email[0] : 'Ocurrió un error al procesar tu solicitud';
                    showErrorMessage(errorMsg);
                },
            });
        } catch (error) {
            showErrorMessage('Error de conexión. Por favor, intenta nuevamente.');
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleInputChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const currentYear = new Date().getFullYear();

    const cleanPhoneNumber = (phone) => {
        return phone ? phone.replace(/[\s\-]/g, '') : '';
    };

    return (
        <div className="flex h-fit w-full flex-col bg-black">
            <div className="mx-auto h-full w-full max-w-[1200px] grid grid-cols-12 gap-x-20 items-start justify-items-start max-sm:gap-10 px-4 py-10 lg:px-0 lg:py-26">
                {/* Logo y redes sociales */}
                <div className="col-span-2 flex h-full flex-col items-center gap-4">
                    <Link href="/" >
                        <img src={logos?.logo_principal || ''} alt="Logo secundario" className="w-[124px] h-[71px] sm:max-w-full" />
                    </Link>

                    <div className="flex flex-row items-center justify-center gap-4 sm:gap-2">
                        {contacto?.ig && (
                            <a target="_blank" rel="noopener noreferrer" href={contacto.ig} aria-label="Instagram">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M17.5 6.5H17.51M7 2H17C19.7614 2 22 4.23858 22 7V17C22 19.7614 19.7614 22 17 22H7C4.23858 22 2 19.7614 2 17V7C2 4.23858 4.23858 2 7 2ZM16 11.37C16.1234 12.2022 15.9813 13.0522 15.5938 13.799C15.2063 14.5458 14.5931 15.1514 13.8416 15.5297C13.0901 15.9079 12.2384 16.0396 11.4078 15.9059C10.5771 15.7723 9.80976 15.3801 9.21484 14.7852C8.61992 14.1902 8.22773 13.4229 8.09407 12.5922C7.9604 11.7616 8.09207 10.9099 8.47033 10.1584C8.84859 9.40685 9.45419 8.79374 10.201 8.40624C10.9478 8.01874 11.7978 7.87659 12.63 8C13.4789 8.12588 14.2649 8.52146 14.8717 9.12831C15.4785 9.73515 15.8741 10.5211 16 11.37Z"
                                        stroke="white" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"
                                    />
                                </svg>
                            </a>
                        )}

                        {contacto?.fb && (
                            <a target="_blank" rel="noopener noreferrer" href={contacto.fb} aria-label="Facebook">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M18 2H15C13.6739 2 12.4021 2.52678 11.4645 3.46447C10.5268 4.40215 10 5.67392 10 7V10H7V14H10V22H14V14H17L18 10H14V7C14 6.73478 14.1054 6.48043 14.2929 6.29289C14.4804 6.10536 14.7348 6 15 6H18V2Z"
                                        fill="white"
                                    />
                                </svg>
                            </a>
                        )}
                    </div>
                </div>

                {/* Secciones - Mobile */}
                <div className="flex flex-col items-center gap-6 sm:hidden col-span-12">
                    <h2 className="text-lg font-bold text-white">Secciones</h2>
                    <div className="flex flex-wrap justify-center gap-x-6 gap-y-4">
                        <Link href="/nosotros" className="text-[15px] text-white/80">Nosotros</Link>
                        <Link href="/productos" className="text-[15px] text-white/80">Productos</Link>
                        <Link href="/calidad" className="text-[15px] text-white/80">Novedades</Link>
                        <Link href="/catalogos" className="text-[15px] text-white/80">Catálogos</Link>
                        <Link href="/contacto" className="text-[15px] text-white/80">Contacto</Link>
                    </div>
                </div>

                {/* Secciones y Newsletter - Desktop */}
                <div className="col-span-3 flex flex-col gap-10 w-fit">
                    {/* Secciones - Desktop */}
                    <div className="hidden flex-col gap-10 lg:flex">
                        <h2 className="text-lg font-bold text-white">Secciones</h2>
                        <div className="grid h-fit grid-cols-2 gap-x-20 gap-y-3">
                            <div className="flex flex-col gap-y-2">
                                <Link href="/nosotros" className="text-[15px] text-white/80">Nosotros</Link>
                                <Link href="/productos" className="text-[15px] text-white/80">Productos</Link>
                                <Link href="/calidad" className="text-[15px] text-white/80">Novedades</Link>
                            </div>
                            <div className="flex flex-col gap-y-2">
                                <Link href="/catalogos" className="text-[15px] text-white/80">Catálogos</Link>
                                <Link href="/contacto" className="text-[15px] text-white/80">Contacto</Link>
                            </div>
                        </div>
                    </div>

                    {/* Newsletter */}
                    <div className="flex h-full w-full flex-col items-center gap-6 lg:items-start lg:gap-8">
                        <h2 className="text-lg font-bold text-white uppercase">Suscribite al Newsletter</h2>

                        {/* Mensaje de confirmación */}
                        {showSuccess && (
                            <div className="w-full sm:w-[287px] p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                                <div className="flex items-center">
                                    <svg className="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fillRule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clipRule="evenodd"
                                        />
                                    </svg>
                                    <span className="text-sm">¡Te suscribiste correctamente al newsletter!</span>
                                </div>
                            </div>
                        )}

                        {/* Mensaje de error */}
                        {showError && (
                            <div className="w-full sm:w-[287px] p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                                <div className="flex items-center">
                                    <svg className="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fillRule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clipRule="evenodd"
                                        />
                                    </svg>
                                    <span className="text-sm">{errorMessage}</span>
                                </div>
                            </div>
                        )}

                        {/* Formulario del newsletter */}
                        <form
                            onSubmit={handleSubmit}
                            className="flex h-[44px] w-full items-center justify-between outline outline-white rounded-lg px-4 sm:w-[287px] bg-transparent"
                        >
                            <input
                                name="email"
                                type="email"
                                required
                                value={formData.email}
                                onChange={handleInputChange}
                                className="w-full outline-none placeholder:text-white text-white bg-transparent"
                                placeholder="Ingresa tu email"
                            />
                            <button type="submit" disabled={isSubmitting}>
                                {isSubmitting ? (
                                    <svg className="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
                                        <path
                                            className="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        />
                                    </svg>
                                ) : (
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path
                                            d="M4.1665 10.0001H15.8332M15.8332 10.0001L9.99984 4.16675M15.8332 10.0001L9.99984 15.8334"
                                            stroke="#FF120B" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"
                                        />
                                    </svg>
                                )}
                            </button>
                        </form>
                    </div>
                </div>

                {/* Datos de contacto */}
                <div className="col-span-7 flex h-full flex-col items-center gap-6 lg:items-start lg:gap-10 self-center w-full">
                    <h2 className="text-lg font-bold text-white">Datos de contacto</h2>
                    <div className="grid grid-cols-2 gap-4 w-full">
                        {contacto?.location && (
                            <a
                                href={`https://maps.google.com/?q=${encodeURIComponent(contacto.location)}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="flex items-center gap-3 transition-opacity hover:opacity-80 max-w-[326px] col-span-2"
                            >
                                <div className="shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path
                                            d="M16.6666 8.33335C16.6666 13.3334 9.99992 18.3334 9.99992 18.3334C9.99992 18.3334 3.33325 13.3334 3.33325 8.33335C3.33325 6.56524 4.03563 4.86955 5.28587 3.61931C6.53612 2.36907 8.23181 1.66669 9.99992 1.66669C11.768 1.66669 13.4637 2.36907 14.714 3.61931C15.9642 4.86955 16.6666 6.56524 16.6666 8.33335Z"
                                            stroke="#FF120B" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"
                                        />
                                        <path
                                            d="M10 10.8333C11.3807 10.8333 12.5 9.71402 12.5 8.33331C12.5 6.9526 11.3807 5.83331 10 5.83331C8.61929 5.83331 7.5 6.9526 7.5 8.33331C7.5 9.71402 8.61929 10.8333 10 10.8333Z"
                                            stroke="#FF120B" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"
                                        />
                                    </svg>
                                </div>
                                <p className="text-base text-white/80 break-words">{contacto.location}</p>
                            </a>
                        )}

                        {contacto?.mail && (
                            <a href={`mailto:${contacto.mail}`} className="flex items-center gap-3 transition-opacity hover:opacity-80 col-span-2">
                                <div className="shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path
                                            d="M16.6667 3.33331H3.33341C2.41294 3.33331 1.66675 4.0795 1.66675 4.99998V15C1.66675 15.9205 2.41294 16.6666 3.33341 16.6666H16.6667C17.5872 16.6666 18.3334 15.9205 18.3334 15V4.99998C18.3334 4.0795 17.5872 3.33331 16.6667 3.33331Z"
                                            stroke="#FF120B" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"
                                        />
                                        <path
                                            d="M18.3334 5.83331L10.8584 10.5833C10.6011 10.7445 10.3037 10.83 10.0001 10.83C9.69648 10.83 9.39902 10.7445 9.14175 10.5833L1.66675 5.83331"
                                            stroke="#FF120B" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"
                                        />
                                    </svg>
                                </div>
                                <p className="text-base text-white/80 break-words">{contacto.mail}</p>
                            </a>
                        )}
                        
                        {contacto?.wp_adm && (
                            <a
                                href={`https://wa.me/${cleanPhoneNumber(contacto.wp_adm)}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="flex items-center gap-3 transition-opacity hover:opacity-80 whitespace-nowrap w-full"
                            >
                                <div className="shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path
                                            d="M17 2.91005C16.0831 1.98416 14.991 1.25002 13.7875 0.750416C12.584 0.250812 11.2931 -0.00426317 9.99 5.38951e-05C4.53 5.38951e-05 0.0800002 4.45005 0.0800002 9.91005C0.0800002 11.6601 0.54 13.3601 1.4 14.8601L0 20.0001L5.25 18.6201C6.7 19.4101 8.33 19.8301 9.99 19.8301C15.45 19.8301 19.9 15.3801 19.9 9.92005C19.9 7.27005 18.87 4.78005 17 2.91005ZM9.99 18.1501C8.51 18.1501 7.06 17.7501 5.79 17.0001L5.49 16.8201L2.37 17.6401L3.2 14.6001L3 14.2901C2.17755 12.9771 1.74092 11.4593 1.74 9.91005C1.74 5.37005 5.44 1.67005 9.98 1.67005C12.18 1.67005 14.25 2.53005 15.8 4.09005C16.5676 4.85392 17.1759 5.7626 17.5896 6.76338C18.0033 7.76417 18.2142 8.83714 18.21 9.92005C18.23 14.4601 14.53 18.1501 9.99 18.1501ZM14.51 11.9901C14.26 11.8701 13.04 11.2701 12.82 11.1801C12.59 11.1001 12.43 11.0601 12.26 11.3001C12.09 11.5501 11.62 12.1101 11.48 12.2701C11.34 12.4401 11.19 12.4601 10.94 12.3301C10.69 12.2101 9.89 11.9401 8.95 11.1001C8.21 10.4401 7.72 9.63005 7.57 9.38005C7.43 9.13005 7.55 9.00005 7.68 8.87005C7.79 8.76005 7.93 8.58005 8.05 8.44005C8.17 8.30005 8.22 8.19005 8.3 8.03005C8.38 7.86005 8.34 7.72005 8.28 7.60005C8.22 7.48005 7.72 6.26005 7.52 5.76005C7.32 5.28005 7.11 5.34005 6.96 5.33005H6.48C6.31 5.33005 6.05 5.39005 5.82 5.64005C5.6 5.89005 4.96 6.49005 4.96 7.71005C4.96 8.93005 5.85 10.1101 5.97 10.2701C6.09 10.4401 7.72 12.9401 10.2 14.0101C10.79 14.2701 11.25 14.4201 11.61 14.5301C12.2 14.7201 12.74 14.6901 13.17 14.6301C13.65 14.5601 14.64 14.0301 14.84 13.4501C15.05 12.8701 15.05 12.3801 14.98 12.2701C14.91 12.1601 14.76 12.1101 14.51 11.9901Z"
                                            fill="#FF120B"
                                        />
                                    </svg>
                                </div>
                                <p className="text-base text-white/80 break-words">Administración: {contacto.wp_adm}</p>
                            </a>
                        )}
                        
                        {contacto?.mail_adm && (
                            <a href={`mailto:${contacto.mail_adm}`} className="flex items-center gap-3 transition-opacity hover:opacity-80">
                                <div className="shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path
                                            d="M16.6667 3.33331H3.33341C2.41294 3.33331 1.66675 4.0795 1.66675 4.99998V15C1.66675 15.9205 2.41294 16.6666 3.33341 16.6666H16.6667C17.5872 16.6666 18.3334 15.9205 18.3334 15V4.99998C18.3334 4.0795 17.5872 3.33331 16.6667 3.33331Z"
                                            stroke="#FF120B" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"
                                        />
                                        <path
                                            d="M18.3334 5.83331L10.8584 10.5833C10.6011 10.7445 10.3037 10.83 10.0001 10.83C9.69648 10.83 9.39902 10.7445 9.14175 10.5833L1.66675 5.83331"
                                            stroke="#FF120B" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"
                                        />
                                    </svg>
                                </div>
                                <p className="text-base text-white/80 break-words">{contacto.mail_adm}</p>
                            </a>
                        )}

                        {contacto?.wp_ventas && (
                            <a
                                href={`https://wa.me/${cleanPhoneNumber(contacto.wp_ventas)}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="flex items-center gap-3 transition-opacity hover:opacity-80"
                            >
                                <div className="shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path
                                            d="M17 2.91005C16.0831 1.98416 14.991 1.25002 13.7875 0.750416C12.584 0.250812 11.2931 -0.00426317 9.99 5.38951e-05C4.53 5.38951e-05 0.0800002 4.45005 0.0800002 9.91005C0.0800002 11.6601 0.54 13.3601 1.4 14.8601L0 20.0001L5.25 18.6201C6.7 19.4101 8.33 19.8301 9.99 19.8301C15.45 19.8301 19.9 15.3801 19.9 9.92005C19.9 7.27005 18.87 4.78005 17 2.91005ZM9.99 18.1501C8.51 18.1501 7.06 17.7501 5.79 17.0001L5.49 16.8201L2.37 17.6401L3.2 14.6001L3 14.2901C2.17755 12.9771 1.74092 11.4593 1.74 9.91005C1.74 5.37005 5.44 1.67005 9.98 1.67005C12.18 1.67005 14.25 2.53005 15.8 4.09005C16.5676 4.85392 17.1759 5.7626 17.5896 6.76338C18.0033 7.76417 18.2142 8.83714 18.21 9.92005C18.23 14.4601 14.53 18.1501 9.99 18.1501ZM14.51 11.9901C14.26 11.8701 13.04 11.2701 12.82 11.1801C12.59 11.1001 12.43 11.0601 12.26 11.3001C12.09 11.5501 11.62 12.1101 11.48 12.2701C11.34 12.4401 11.19 12.4601 10.94 12.3301C10.69 12.2101 9.89 11.9401 8.95 11.1001C8.21 10.4401 7.72 9.63005 7.57 9.38005C7.43 9.13005 7.55 9.00005 7.68 8.87005C7.79 8.76005 7.93 8.58005 8.05 8.44005C8.17 8.30005 8.22 8.19005 8.3 8.03005C8.38 7.86005 8.34 7.72005 8.28 7.60005C8.22 7.48005 7.72 6.26005 7.52 5.76005C7.32 5.28005 7.11 5.34005 6.96 5.33005H6.48C6.31 5.33005 6.05 5.39005 5.82 5.64005C5.6 5.89005 4.96 6.49005 4.96 7.71005C4.96 8.93005 5.85 10.1101 5.97 10.2701C6.09 10.4401 7.72 12.9401 10.2 14.0101C10.79 14.2701 11.25 14.4201 11.61 14.5301C12.2 14.7201 12.74 14.6901 13.17 14.6301C13.65 14.5601 14.64 14.0301 14.84 13.4501C15.05 12.8701 15.05 12.3801 14.98 12.2701C14.91 12.1601 14.76 12.1101 14.51 11.9901Z"
                                            fill="#FF120B"
                                        />
                                    </svg>
                                </div>
                                <p className="text-base text-white/80 break-words">Ventas: {contacto.wp_ventas}</p>
                            </a>
                        )}

                        {contacto?.mail_ventas && (
                            <a href={`mailto:${contacto.mail_ventas}`} className="flex items-center gap-3 transition-opacity hover:opacity-80">
                                <div className="shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path
                                            d="M16.6667 3.33331H3.33341C2.41294 3.33331 1.66675 4.0795 1.66675 4.99998V15C1.66675 15.9205 2.41294 16.6666 3.33341 16.6666H16.6667C17.5872 16.6666 18.3334 15.9205 18.3334 15V4.99998C18.3334 4.0795 17.5872 3.33331 16.6667 3.33331Z"
                                            stroke="#FF120B" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"
                                        />
                                        <path
                                            d="M18.3334 5.83331L10.8584 10.5833C10.6011 10.7445 10.3037 10.83 10.0001 10.83C9.69648 10.83 9.39902 10.7445 9.14175 10.5833L1.66675 5.83331"
                                            stroke="#FF120B" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"
                                        />
                                    </svg>
                                </div>
                                <p className="text-base text-white/80 break-words">{contacto.mail_ventas}</p>
                            </a>
                        )}

                        {contacto?.wp_tlk && (
                            <a
                                href={`https://wa.me/${cleanPhoneNumber(contacto.wp_tlk)}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="flex items-center gap-3 transition-opacity hover:opacity-80"
                            >
                                <div className="shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path
                                            d="M17 2.91005C16.0831 1.98416 14.991 1.25002 13.7875 0.750416C12.584 0.250812 11.2931 -0.00426317 9.99 5.38951e-05C4.53 5.38951e-05 0.0800002 4.45005 0.0800002 9.91005C0.0800002 11.6601 0.54 13.3601 1.4 14.8601L0 20.0001L5.25 18.6201C6.7 19.4101 8.33 19.8301 9.99 19.8301C15.45 19.8301 19.9 15.3801 19.9 9.92005C19.9 7.27005 18.87 4.78005 17 2.91005ZM9.99 18.1501C8.51 18.1501 7.06 17.7501 5.79 17.0001L5.49 16.8201L2.37 17.6401L3.2 14.6001L3 14.2901C2.17755 12.9771 1.74092 11.4593 1.74 9.91005C1.74 5.37005 5.44 1.67005 9.98 1.67005C12.18 1.67005 14.25 2.53005 15.8 4.09005C16.5676 4.85392 17.1759 5.7626 17.5896 6.76338C18.0033 7.76417 18.2142 8.83714 18.21 9.92005C18.23 14.4601 14.53 18.1501 9.99 18.1501ZM14.51 11.9901C14.26 11.8701 13.04 11.2701 12.82 11.1801C12.59 11.1001 12.43 11.0601 12.26 11.3001C12.09 11.5501 11.62 12.1101 11.48 12.2701C11.34 12.4401 11.19 12.4601 10.94 12.3301C10.69 12.2101 9.89 11.9401 8.95 11.1001C8.21 10.4401 7.72 9.63005 7.57 9.38005C7.43 9.13005 7.55 9.00005 7.68 8.87005C7.79 8.76005 7.93 8.58005 8.05 8.44005C8.17 8.30005 8.22 8.19005 8.3 8.03005C8.38 7.86005 8.34 7.72005 8.28 7.60005C8.22 7.48005 7.72 6.26005 7.52 5.76005C7.32 5.28005 7.11 5.34005 6.96 5.33005H6.48C6.31 5.33005 6.05 5.39005 5.82 5.64005C5.6 5.89005 4.96 6.49005 4.96 7.71005C4.96 8.93005 5.85 10.1101 5.97 10.2701C6.09 10.4401 7.72 12.9401 10.2 14.0101C10.79 14.2701 11.25 14.4201 11.61 14.5301C12.2 14.7201 12.74 14.6901 13.17 14.6301C13.65 14.5601 14.64 14.0301 14.84 13.4501C15.05 12.8701 15.05 12.3801 14.98 12.2701C14.91 12.1601 14.76 12.1101 14.51 11.9901Z"
                                            fill="#FF120B"
                                        />
                                    </svg>
                                </div>
                                <p className="text-base text-white/80 break-words">Telemarketing: {contacto.wp_tlk}</p>
                            </a>
                        )}

                        {contacto?.mail_tlk && (
                            <a href={`mailto:${contacto.mail_tlk}`} className="flex items-center gap-3 transition-opacity hover:opacity-80">
                                <div className="shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path
                                            d="M16.6667 3.33331H3.33341C2.41294 3.33331 1.66675 4.0795 1.66675 4.99998V15C1.66675 15.9205 2.41294 16.6666 3.33341 16.6666H16.6667C17.5872 16.6666 18.3334 15.9205 18.3334 15V4.99998C18.3334 4.0795 17.5872 3.33331 16.6667 3.33331Z"
                                            stroke="#FF120B" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"
                                        />
                                        <path
                                            d="M18.3334 5.83331L10.8584 10.5833C10.6011 10.7445 10.3037 10.83 10.0001 10.83C9.69648 10.83 9.39902 10.7445 9.14175 10.5833L1.66675 5.83331"
                                            stroke="#FF120B" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"
                                        />
                                    </svg>
                                </div>
                                <p className="text-base text-white/80 break-words">{contacto.mail_tlk}</p>
                            </a>
                        )}

                    </div>
                </div>
            </div>

            {/* Copyright */}
            <div className="flex min-h-[67px] w-full flex-col items-center justify-center bg-black px-4 py-4 text-[14px] text-white/80 sm:flex-row sm:justify-between sm:px-6 lg:px-0">
                <div className="mx-auto flex w-full max-w-[1200px] flex-col items-center justify-center gap-2 text-center sm:flex-row sm:justify-between sm:gap-0 sm:text-left">
                    <p>© Copyright {currentYear} SGB. Todos los derechos reservados</p>
                    <a target="_blank" rel="noopener noreferrer" href="https://osole.com.ar/" className="mt-2 sm:mt-0">
                        By <span className="font-bold">Osole</span>
                    </a>
                </div>
            </div>
        </div>
    );
};

export default Footer;