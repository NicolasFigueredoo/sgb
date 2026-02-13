<?php

namespace App\Http\Controllers;

use App\Models\Categoria;

use App\Models\ImagenProducto;
use App\Models\Marca;
use App\Models\Metadatos;
use App\Models\Modelo;
use App\Models\Motor;
use App\Models\Producto;
use App\Models\ProductoMarca;
use App\Models\ProductoModelo;
use App\Models\ProductoMotor;
use App\Models\SubCategoria;
use App\Models\SubProducto;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

use function Pest\Laravel\get;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $categorias = Categoria::select('id', 'name')->get();
        $marcas = Marca::orderBy('order', 'asc')->get();
        $modelos = Modelo::orderBy('order', 'asc')->get();

        $motores = Motor::orderBy('order', 'asc')->get();
        $perPage = $request->input('per_page',  10);

        $query = Producto::query()->orderBy('order', 'asc')->with(['marca', 'modelos', 'motores', 'categoria', 'imagenes']);

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('name', 'LIKE', '%' . $searchTerm . '%')->orWhere('code', 'LIKE', '%' . $searchTerm . '%');
        }

        $productos = $query->paginate($perPage);





        return Inertia::render('admin/productosAdmin', [
            'productos' => $productos,
            'categorias' => $categorias,
            'marcas' => $marcas,
            'modelos' => $modelos,
            'motores' => $motores,
        ]);
    }

    public function indexCategorias(Request $request)
    {
        $categorias = Categoria::orderBy('order', 'asc')->get();
        $marcas = Marca::orderBy('order', 'asc')->get();
        $modelos = Modelo::orderBy('order', 'asc')->get();
        $motores = Motor::orderBy('order', 'asc')->get();

        return view('productos-categorias', [
            'categorias' => $categorias,
            'marcas' => $marcas,
            'modelos' => $modelos,
            'motores' => $motores,
        ]);
    }

    public function indexVistaPrevia(Request $request)
    {
        try {
            // Construir query base para productos
            $query = Producto::query();

            // Filtro por categoría (a través de marcas)
            if ($request->filled('tipo')) {
                $query->where('categoria_id', $request->tipo);
            }

            // Filtro por modelo/subcategoría
            if ($request->filled('marca')) {
                $query->where('marca_id', $request->marca);
            }

            if ($request->filled('modelo')) {
                $query->whereHas('modelos', function ($q) use ($request) {
                    $q->where('modelo_id', $request->modelo);
                });
            }

            if ($request->filled('motor')) {
                $query->whereHas('motores', function ($q) use ($request) {
                    $q->where('motor_id', $request->motor);
                });
            }

            // Filtro por código
            if ($request->filled('code')) {
                $query->where('code', 'LIKE', '%' . $request->code . '%');
            }

            // Filtro por código OEM
            if ($request->filled('code_oem')) {
                $query->where('code_oem', 'LIKE', '%' . $request->code_oem . '%');
            }

            // Aplicar ordenamiento por defecto
            $query->orderBy('order', 'asc');

            // Ejecutar query con paginación
            $productos = $query->with(['marca', 'modelos', 'imagenes', 'categoria'])
                ->paginate(15)
                ->appends($request->query());

            // Cargar datos adicionales para la vista
            $categorias = Categoria::with('subCategorias')->orderBy('order', 'asc')->get();
            $marcas = Marca::orderBy('order', 'asc')->get();
            $modelos = Modelo::orderBy('order', 'asc')->get();
            $motores = Motor::orderBy('order', 'asc')->get();

            return view('productos', [
                'categorias' => $categorias,
                'productos' => $productos,
                'tipo' => $request->tipo,
                'marca' => $request->marca,
                'modelo' => $request->modelo,
                'code' => $request->code,
                'code_oem' => $request->code_oem,
                'marcas' => $marcas,
                'modelos' => $modelos,
                'motores' => $motores,
                'motor' => $request->motor,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cargar la vista previa de productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($codigo, Request $request)
    {
        try {
        $producto = Producto::with(['categoria:id,name', 'imagenes', 'marca', 'modelos'])->where('code', $codigo)->first();

        $subcategorias = SubCategoria::orderBy('order', 'asc')->get();

        $categorias = Categoria::select('id', 'name', 'order')->orderBy('order', 'asc')->get();

        // Obtener productos relacionados por marca y modelo
        $productosRelacionados = Producto::where('id', '!=', $producto->id)->with(['categoria:id,name', 'imagenes', 'marca', 'modelos'])->orderBy('order', 'asc')->limit(3)->get();
        
        return view('producto', [
            'producto' => $producto,
            'categorias' => $categorias,
            'subcategorias' => $subcategorias,
            'categoria' => $producto->categoria,
            'productosRelacionados' => $productosRelacionados,
            'modelo_id' => $request->modelo_id ?? null
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cargar el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function indexPrivada(Request $request)
    {


        $perPage = $request->input('per_page', 10);

        $qty = $request->input('qty', 1); // Valor por defecto para qty
        $carrito = Cart::content();

        $query = Producto::with(['imagenes', 'marca', 'modelos', 'categoria'])
    ->orderByRaw("CASE 
        WHEN productos.precio IS NULL OR productos.precio <= 0 THEN 1 
        ELSE 0 
    END ASC")
    ->orderBy('order', 'asc');


        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->tipo);
        }

        // Filtro por modelo/subcategoría
        if ($request->filled('marca')) {
            $query->where('marca_id', $request->marca);
        }

        if ($request->filled('modelo')) {
            $query->whereHas('modelos', function ($q) use ($request) {
                $q->where('modelo_id', $request->modelo);
            });
        }

        if ($request->filled('motor')) {
            $query->whereHas('motores', function ($q) use ($request) {
                $q->where('motor_id', $request->motor);
            });
        }

        // Filtro por código
        if ($request->filled('code')) {
            $query->where('code', 'LIKE', '%' . $request->code . '%');
        }

        // Filtro por código OEM
        if ($request->filled('code_oem')) {
            $query->where('code_oem', 'LIKE', '%' . $request->code_sr . '%');
        }



        $productos = $query->paginate(perPage: $perPage);

        // Modificar los productos para agregar rowId y qty del carrito
        $productos->getCollection()->transform(function ($producto) use ($carrito, $qty) {
            // Buscar el item del carrito que corresponde a este producto
            $itemCarrito = $carrito->where('id', $producto->id)->first();

            $tieneOfertaVigente = $producto->ofertas()
                ->where('user_id', Auth::id())
                ->where('fecha_fin', '>', now())
                ->exists();

            if ($itemCarrito) {
                $producto->rowId = $itemCarrito ? $itemCarrito->rowId : null;
                $producto->qty = $itemCarrito ? $itemCarrito->qty : null;
                $producto->subtotal =  $itemCarrito ? $itemCarrito->price * ($itemCarrito->qty ?? 1) : $producto->precio;
            } else {
                $producto->rowId = null;
                $producto->qty = $qty; // Asignar qty por defecto si no está en el carrito
                $producto->subtotal = $producto->precio ? $producto->precio * ($producto->qty ?? 1) : 0; // Asignar precio base si no está en el carrito
            }

            if ($tieneOfertaVigente) {
                $producto->oferta = true;
            }


            return $producto;
        });
        # si el usuario es vendedor

        $categorias = Categoria::orderBy('order', 'asc')->get();
        $marcas = Marca::orderBy('order', 'asc')->get();
        $modelos = Modelo::orderBy('order', 'asc')->get();
        $motores = Motor::orderBy('order', 'asc')->get();
        $userId = Auth::id();

        $productosOferta = Producto::where('oferta', true)
            ->with(['imagenes', 'marca', 'modelos'])
            ->orderBy('order', 'asc')
            ->get();

        return inertia('privada/productosPrivada', [
            'productos' => $productos,
            'categorias' => $categorias,
            'productosOferta' => $productosOferta,
            'categoria' => $request->categoria,
            'marca' => $request->marca,
            'modelo' => $request->modelo,
            'code' => $request->code,
            'code_oem' => $request->code_oem,
            'motor' => $request->motor,
            'marcas' => $marcas,
            'modelos' => $modelos,
            'motores' => $motores,

        ]);
    }

    /* public function indexInicio(Request $request, $id)
    {
        $marcas = Marca::select('id', 'name', 'order')->orderBy('order', 'asc')->get();

        $categorias = Categoria::select('id', 'name', 'order')
            ->orderBy('order', 'asc')
            ->get();
        $metadatos = Metadatos::where('title', 'Productos')->first();
        if ($request->has('marca') && !empty($request->marca)) {
            $productos = Producto::where('categoria_id', $id)->whereHas('subproductos')->whereHas('imagenes')->where('marca_id', $request->marca)->with('marca', 'imagenes')->orderBy('order', 'asc')->get();
        } else {
            $productos = Producto::where('categoria_id', $id)->whereHas('subproductos')->whereHas('imagenes')->with('marca', 'imagenes')->orderBy('order', 'asc')->get();
        }
        $subproductos = SubProducto::orderBy('order', 'asc')->get();

        return Inertia::render('productos', [
            'productos' => $productos,
            'categorias' => $categorias,
            'marcas' => $marcas,
            'metadatos' => $metadatos,
            'id' => $id,
            'marca_id' => $request->marca,
            'subproductos' => $subproductos,

        ]);
    } */

    public function indexInicio(Request $request, $id)
    {


        $categorias = Categoria::select('id', 'name', 'order')
            ->orderBy('order', 'asc')
            ->get();

        $metadatos = Metadatos::where('title', 'Productos')->first();

        $query = Producto::where('categoria_id', $id)

            ->orderBy('order', 'asc');



        $productos = $query->paginate(12)->withQueryString(); // 12 por página, mantiene filtros

        // Opcional: solo subproductos de productos actuales (más eficiente)
        $productoIds = $productos->pluck('id');
        $subproductos = SubProducto::whereIn('producto_id', $productoIds)
            ->orderBy('order', 'asc')
            ->get();

        return Inertia::render('productos', [
            'productos' => $productos,
            'categorias' => $categorias,

            'metadatos' => $metadatos,
            'id' => $id,

            'subproductos' => $subproductos,
        ]);
    }

    public function imagenesProducto()
    {
        $fotos = Storage::disk('public')->files('repuestos');

        foreach ($fotos as $foto) {
            $path = pathinfo(basename($foto), PATHINFO_FILENAME);

            $producto = Producto::where('code', $path)->first();
            if (!$producto) {
                continue; // Skip if the product is not found
            }
            $url = Storage::url($foto);
            ImagenProducto::create([
                'producto_id' => $producto->id,
                'image' => $url,
            ]);
        }
    }







    public function SearchProducts(Request $request)
    {
        $query = Producto::query();

        // Aplicar filtros solo si existen
        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }



        if ($request->filled('codigo')) {
            $query->where('code', 'LIKE', '%' . $request->codigo . '%');
        }

        $productos = $query->with(['categoria:id,name', 'imagenes'])
            ->get();

        $categorias = Categoria::select('id', 'name', 'order')->orderBy('order', 'asc')->get();


        return Inertia::render('productos/productoSearch', [
            'productos' => $productos, // Cambié 'producto' a 'productos' (plural)
            'categorias' => $categorias,

        ]);
    }

    public function fixImagePath()
    {
        # Quitar /storage/ de las rutas de las imágenes
        $imagenes = ImagenProducto::all();
        foreach ($imagenes as $imagen) {
            if (strpos($imagen->image, '/storage/') === 0) {
                $imagen->image = str_replace('/storage/', '', $imagen->image);
                $imagen->save();
            }
        }

        return response()->json(['message' => 'Rutas de imágenes actualizadas correctamente.']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        $data = $request->validate([
            // Validaciones del producto
            'order' => 'nullable|sometimes|max:255',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'code_oem' => 'nullable|sometimes|string|max:255',
            'unidad_pack' => 'nullable|integer',
            'destacado' => 'nullable|sometimes|boolean',
            'nuevo' => 'nullable|sometimes|boolean',
            'oferta' => 'nullable|sometimes|boolean',
            'descuento' => 'nullable|sometimes|integer|min:0|max:100',
            'medidas' => 'nullable|string',
            'precio' => 'nullable|numeric|min:0',
            'categoria_id' => 'nullable|exists:categorias,id',
            'marca_id' => 'nullable|exists:marcas,id',
            'modelos' => 'nullable|array',
            'modelos.*' => 'integer|exists:modelos,id',
            'motores' => 'nullable|array',
            'motores.*' => 'integer|exists:motors,id',
            'images' => 'nullable|array|min:1',
            'images.*' => 'required|file|image',
        ]);

        try {
            return DB::transaction(function () use ($request, $data) {
                // Crear el producto primero
                $producto = Producto::create([
                    'order' => $data['order'] ?? 'zzz',
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'code_oem' => $data['code_oem'],
                    'unidad_pack' => $data['unidad_pack'] ?? 1,
                    'destacado' => $data['destacado'] ?? false,
                    'nuevo' => $data['nuevo'] ?? false,
                    'oferta' => $data['oferta'] ?? false,
                    'descuento' => $data['descuento'] ?? 0,
                    'medidas' => $data['medidas'] ?? null,
                    'precio' => $data['precio'] ?? 0.00,
                    'categoria_id' => $data['categoria_id'] ?? null,
                    'marca_id' => $data['marca_id'] ?? null,

                ]);

                $createdImages = [];

                // Procesar imágenes si existen
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $image) {
                        // Subir cada imagen
                        $imagePath = $image->store('images', 'public');

                        // Crear registro para cada imagen usando el ID del producto recién creado
                        $imageRecord = ImagenProducto::create([
                            'producto_id' => $producto->id,
                            'order' => $data['order'] ?? null,
                            'image' => $imagePath,
                        ]);

                        $createdImages[] = $imageRecord;
                    }
                }


                if ($request->has('modelos')) {
                    foreach ($data['modelos'] as $modeloId) {
                        ProductoModelo::create([
                            'producto_id' => $producto->id,
                            'modelo_id' => $modeloId,
                        ]);
                    }
                }

                if ($request->has('motores')) {
                    foreach ($data['motores'] as $motorId) {
                        ProductoMotor::create([
                            'producto_id' => $producto->id,
                            'motor_id' => $motorId,
                        ]);
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function update(Request $request)
    {
        $data = $request->validate([
            // Validaciones del producto

            'order' => 'nullable|sometimes|max:255',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'code_oem' => 'nullable|sometimes|string|max:255',
            'unidad_pack' => 'nullable|integer',
            'destacado' => 'nullable|sometimes|boolean',
            'nuevo' => 'nullable|sometimes|boolean',
            'oferta' => 'nullable|sometimes|boolean',
            'descuento' => 'nullable|sometimes|integer|min:0|max:100',
            'medidas' => 'nullable|string',
            'precio' => 'nullable|numeric|min:0',
            'categoria_id' => 'nullable|exists:categorias,id',
            'marca_id' => 'nullable|exists:marcas,id',
            'motores' => 'nullable|array',
            'motores.*' => 'integer|exists:motors,id',
            'modelos' => 'nullable|array',
            'modelos.*' => 'integer|exists:modelos,id',
            // Validaciones de las imágenes (opcionales)
            'images' => 'nullable|array|min:1',
            'images.*' => 'required|file|image',
            // Para eliminar imágenes existentes
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'integer|exists:imagen_productos,id',
        ]);

        try {
            return DB::transaction(function () use ($request, $data) {
                // Buscar el producto
                $producto = Producto::findOrFail($request->id);

                // Actualizar los datos del producto
                $producto->update([
                    'order' => $data['order'] ?? 'zzz',
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'code_oem' => $data['code_oem'],
                    'unidad_pack' => $data['unidad_pack'] ?? 1,
                    'destacado' => $data['destacado'] ?? false,
                    'nuevo' => $data['nuevo'] ?? false,
                    'oferta' => $data['oferta'] ?? false,
                    'descuento' => $data['descuento'] ?? 0,
                    'medidas' => $data['medidas'] ?? null,
                    'precio' => $data['precio'] ?? 0.00,
                    'categoria_id' => $data['categoria_id'] ?? null,
                    'marca_id' => $data['marca_id'] ?? null,
                ]);

                if ($request->has('images_to_delete')) {
                    foreach ($request->images_to_delete as $imageId) {
                        $image = ImagenProducto::find($imageId);
                        if ($image) {
                            // Eliminar archivo del storage
                            Storage::delete($image->image);
                            // Eliminar registro de la base de datos
                            $image->delete();
                        }
                    }
                }

                // Agregar nuevas imágenes
                if ($request->hasFile('new_images')) {
                    foreach ($request->file('new_images') as $image) {
                        $path = $image->store('images', 'public');

                        ImagenProducto::create([
                            'producto_id' => $producto->id,
                            'image' => $path,
                        ]);
                    }
                }

                // Actualizar otros campos del producto
                if ($request->has('modelos')) {
                    // Obtener los modelos actuales del producto
                    $modelosActuales = ProductoModelo::where('producto_id', $producto->id)
                    ->get();

                    foreach ($modelosActuales as $modelo) {
                        $modelo->delete();
                    }
                    
                    foreach ($data['modelos'] as $modeloId) {
                        ProductoModelo::create([
                            'producto_id' => $producto->id,
                            'modelo_id' => $modeloId,
                        ]);
                    }
                }

                if ($request->has('motores')) {
                    $motoresActuales = ProductoMotor::where('producto_id', $producto->id)
                        ->get();

                    foreach ($motoresActuales as $motor) {
                        $motor->delete();
                    }

                    foreach ($data['motores'] as $motorId) {
                        ProductoMotor::create([
                            'producto_id' => $producto->id,
                            'motor_id' => $motorId,
                        ]);
                    }
                }

                // Eliminar imágenes seleccionadas si se especificaron
                if ($request->has('delete_images')) {
                    $imagesToDelete = ImagenProducto::where('producto_id', $producto->id)
                        ->whereIn('id', $data['delete_images'])
                        ->get();

                    foreach ($imagesToDelete as $imageRecord) {
                        // Eliminar archivo físico
                        if (Storage::disk('public')->exists($imageRecord->image)) {
                            Storage::disk('public')->delete($imageRecord->image);
                        }
                        // Eliminar registro de la base de datos
                        $imageRecord->delete();
                    }
                }

                // Procesar nuevas imágenes si existen
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $image) {
                        // Subir cada imagen
                        $imagePath = $image->store('images', 'public');

                        // Crear registro para cada imagen
                        ImagenProducto::create([
                            'producto_id' => $producto->id,
                            'order' => $data['order'] ?? null,
                            'image' => $imagePath,
                        ]);
                    }
                }

                // Actualizar relaciones con modelos

            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {

        $id = $request->id;
        try {
            return DB::transaction(function () use ($id) {
                // Buscar el producto
                $producto = Producto::findOrFail($id);

                // Eliminar todas las imágenes asociadas
                $imagenes = ImagenProducto::where('producto_id', $producto->id)->get();
                foreach ($imagenes as $imagen) {
                    // Eliminar archivo físico del storage
                    if (Storage::disk('public')->exists($imagen->image)) {
                        Storage::disk('public')->delete($imagen->image);
                    }
                    // Eliminar registro de la base de datos
                    $imagen->delete();
                }

                // Eliminar relaciones con modelos


                // Eliminar el producto
                $producto->delete();
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function cambiarDestacado(Request $request)
    {
        $producto = Producto::findOrFail($request->id);
        $producto->destacado = !$producto->destacado; // Cambiar el estado de destacado
        $producto->save();
    }

    public function cambiarOferta(Request $request)
    {
        $producto = Producto::findOrFail($request->id);
        $producto->oferta = !$producto->oferta;
        $producto->save();
    }

    public function cambiarNuevo(Request $request)
    {
        $producto = Producto::findOrFail($request->id);
        $producto->nuevo = !$producto->nuevo;
        $producto->save();
    }

    public function productoszonaprivada(Request $request)
    {
        return inertia('admin/zonaprivadaProductosAdmin');
    }



    public function handleQR($code)
    {

        $producto = Producto::where('code', $code)->first();

        if (!$producto && $code != "adm") {
            return redirect('/productos');
        } else if ($code == "adm") {
            if (Auth::guard('admin')->check()) {
                return redirect('/admin/dashboard');
            }
            return Inertia::render('admin/login');
        }




        if (Auth::check()) {

            $tieneOfertaVigente = $producto->ofertas()
                ->where('user_id', Auth::id())
                ->where('fecha_fin', '>', now())
                ->exists();

            Cart::add(
                $producto->id,
                $producto->name,
                $producto->unidad_pack ?? 1,
                $tieneOfertaVigente
                    ? $producto->precio * (1 - $producto->descuento_oferta / 100)
                    : $producto->precio,
                0
            );

            // Guardar en base de datos si hay usuario logueado
            if (Auth::check() && !session('cliente_seleccionado')) {
                if (Cart::count() < 0) {
                    Cart::store(Auth::id());
                }
            } else {
                Cart::store(session('cliente_seleccionado')->id);
            }

            return redirect('/privada/carrito');
        } else {
            return redirect('/p/' . $producto->code);
        }
    }
}
