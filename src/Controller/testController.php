<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use App\Entity\Producto;
use Doctrine\ORM\EntityManagerInterface;

class testController extends AbstractController
{

    private $logger;

    public function __construct(LoggerInterface $logger) //Implementar inferfaz
    {
        $this->logger = $logger;
    }


    /**
     * @Route("/test/list", name="test_list")
     * 
     */

     public function list(){
        $response = new Response();
        $response->setContent('<div>Hola mundo</div>');
        return $response;
     }

    /**
     * @Route("/test/list2", name="test_list2")
     * 
     */

     public function list2(Request $request){
        $this->logger->info('envio json'); //En el services.yaml agregar servicio para utilizar este info

        $data = [
            'success' => true,
            'data' => [
                ['id' => 1, 'title' => 'Relatos salvajes'],
                ['id' => 2, 'title' => 'Relatos salvajes2'],
            ],
        ];
    
        return new JsonResponse($data);
     }

     /**
     * @Route("/agregar-productos", name="agregar_productos", methods={"POST"})
     */
    public function agregarProductos(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Obtiene los datos del cuerpo de la solicitud
        $data = json_decode($request->getContent(), true);

        var_dump($data);

        // Verifica si el SKU ya existe
        $existingProduct = $entityManager->getRepository(Producto::class)->findOneBy(['sku' => $data['sku']]);

        if ($existingProduct === null) {
            $now = new \DateTime('now');

            // Crea un nuevo producto
            $producto = new Producto();
            $producto->setSku($data['sku']);
            $producto->setNombreProducto($data['nombre']);
            $producto->setDescripcion($data['descripcion']);
            $producto->setCreatedAt($now);
            $producto->setUpdatedAt($now);

            // Persiste el producto
            $entityManager->persist($producto);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Datos agregados correctamente a la tabla productos.']);
        } else {
            return new JsonResponse(['message' => 'Ya existe un SKU con esos datos.'], 400);
        }
    }

    /**
     * @Route("/eliminar-producto", name="eliminar_producto", methods={"DELETE"})
     */
    public function eliminarProducto(EntityManagerInterface $entityManager, Request $request): Response
    {
        $ids = $request->query->get('id', '');

        // Asegúrate de que $ids sea un array
        $ids = explode(',', $ids);

        // Obtener los productos según los IDs proporcionados
        $productos = $entityManager->getRepository(Producto::class)->findBy(['id' => $ids]);

        foreach ($productos as $producto) {
            if ($producto !== null) {
                $entityManager->remove($producto);
            }
        }

        $entityManager->flush();

        return new Response("Productos con ID(s) '" . implode(', ', $ids) . "' eliminados correctamente.");
    }


    /**
     * @Route("/editar-producto/{sku}", name="editar_producto")
     */
    public function editarProducto(EntityManagerInterface $entityManager, $sku): Response
    {
        $producto = $entityManager->getRepository(Producto::class)->findOneBy(['sku' => $sku]);
        $now = new \DateTime();

        if ($producto !== null) {
            // Modifica los datos del producto según tus necesidades
            $producto->setNombreProducto('Nuevo Nombre');
            $producto->setDescripcion('Nueva Descripción');
            $producto1->setUpdatedAt($now); // Establece la fecha y hora actual

            $entityManager->flush();

            return new Response("Producto con SKU '{$sku}' editado correctamente.");
        } else {
            return new Response("No se encontró un producto con SKU '{$sku}'.");
        }
    }

    /**
     * @Route("/productos", name="listar_productos")
     */
    public function listarProductos(EntityManagerInterface $entityManager): JsonResponse
    {
        $productos = $entityManager->getRepository(Producto::class)->findAll();

        $data = [];
        foreach ($productos as $producto) {
            $data[] = [
                'id' => $producto->getId(),
                'sku' => $producto->getSku(),
                'nombre_producto' => $producto->getNombreProducto(),
                'descripcion' => $producto->getDescripcion(),
                'created_at' => $producto->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $producto->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($data);
    }

}