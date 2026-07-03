<?php 

return  [
    '/' => [
        'GET' => ['controller' => 'HomeController', 'method' => 'index']
    ],
    '/login' => [
        'GET' => ['controller' => 'LoginController', 'method' => 'index', 'middlewares' => ['guest']], 
        'POST' => ['controller' => 'LoginController', 'method' => 'store', 'middlewares' => ['guest']]
    ],

    '/logout' => [
        'POST' => ['controller' => 'LogoutController', 'method' => 'store', 'middlewares' => ['auth']]
    ], 
    '/register' => [
        'GET' => ['controller' => 'RegisterController', 'method' => 'index', 'middlewares' => ['guest']], 
        'POST' => ['controller' => 'RegisterController', 'method' => 'store', 'middlewares' => ['guest']]
    ],
    '/admin/categories' => [
        'GET' => ['controller' => 'AdminCategoryController', 'method' => 'index', 'middlewares' => []],  // list all categories
        'POST' => ['controller' => 'AdminCategoryController', 'method' => 'store', 'middlewares' => []]  // store category
    ], 
    '/admin/categories/create' => [
        'GET' => ['controller' => 'AdminCategoryController', 'method' => 'create', 'middlewares' => []] // create form
    ], 
    '/admin/categories/edit' => [
        'GET' => ['controller' => 'AdminCategoryController', 'method' => 'edit', 'middlewares' => []]
    ], 
    '/admin/categories/update' => [
        'POST' => ['controller' => 'AdminCategoryController', 'method' => 'update', 'middlewares' => []]
    ], 
    '/admin/categories/delete' => [
        'POST' => ['controller' => 'AdminCategoryController', 'method' => 'destroy', 'middlewares' => []]
    ], 
    '/admin/categories/trash' => [
        'GET' => ['controller' => 'AdminCategoryController', 'method' => 'trash', 'middlewares' => []] // show deleted categories
    ], 
    '/admin/categories/restore' => [
        'POST' => ['controller' => 'AdminCategoryController', 'method' => 'restore', 'middlewares' => []]
    ], 
    '/admin/products' => [
        'GET' => ['controller' => 'AdminProductController', 'method' => 'index', 'middlewares' => []], 
        'POST' => ['controller' => 'AdminProductController', 'method' => 'store', 'middlewares' => []]
    ], 
    '/admin/products/create' => [
        'GET' => ['controller' => 'AdminProductController', 'method' => 'create', 'middlewares' => []]
    ],
    '/admin/products/edit' => [
        'GET' => ['controller' => 'AdminProductController', 'method' => 'edit' , 'middlewares' => []]
    ], 
    '/admin/products/update' => [
        'POST' => ['controller' => 'AdminProductController', 'method' => 'update' , 'middlewares' => []]
    ], 
    '/admin/products/destroy' => [
        'POST' => ['controller' => 'AdminProductController', 'method' => 'destroy' , 'middlewares' => []]
    ], 
    '/admin/products/trash' => [
        'GET' => ['controller' => 'AdminProductController', 'method' => 'trash' , 'middlewares' => []]
    ], 
    '/admin/products/restore' => [
        'POST' => ['controller' => 'AdminProductController', 'method' => 'restore' , 'middlewares' => []]
    ], 
    '/admin/productvariants' => [
        'GET' => ['controller' => 'AdminProductVariantsController', 'method' => 'index', 'middlewares' => []], 
        'POST' => ['controller' => 'AdminProductVariantsController', 'method' => 'store', 'middlewares' => []]
    ], 
    '/admin/productvariants/create' => [
        'GET' => ['controller' => 'AdminProductVariantsController', 'method' => 'create', 'middlewares' => []]
    ],
    '/admin/productvariants/edit' => [
        'GET' => ['controller' => 'AdminProductVariantsController', 'method' => 'edit', 'middlewares' => []]
        
    ], 
    '/admin/productvariants/update' => [
        'POST' => ['controller' => 'AdminProductVariantsController', 'method' => 'update', 'middlewares' => []]
        
    ], 
    '/admin/productvariants/destroy' => [
        'POST' => ['controller' => 'AdminProductVariantsController', 'method' => 'destroy', 'middlewares' => []]
        
    ], 
    '/admin/productvariants/trash' => [
        'GET' => ['controller' => 'AdminProductVariantsController', 'method' => 'trash', 'middlewares' => []]
        
    ], 
    '/admin/productvariants/restore' => [
        'POST' => ['controller' => 'AdminProductVariantsController', 'method' => 'restore', 'middlewares' => []]
        
    ], 
    '/products' => [
        'GET' => ['controller' => 'ClientProductsController', 'method' => 'index', 'middlewares' => []], 

    ], 
    '/products/show' => [
        'GET' => ['controller' => 'ClientProductsController', 'method' => 'show', 'middlewares' => []]
    ], 
    '/cart/add' => [
        'POST' => ['controller' => 'CartController', 'method' => 'add', 'middlewares' => ['auth']]
    ], 
    '/cart' => [
        'GET' => ['controller' => 'CartController', 'method' => 'index', 'middlewares' => ['auth']], 
    ], 
    '/cart/increment' => [
        'POST' => ['controller' => 'CartController', 'method' => 'increment', 'middlewares' => ['auth']], 
    ],
    '/cart/decrement' => [
        'POST' => ['controller' => 'CartController', 'method' => 'decrement', 'middlewares' => ['auth']], 
    ], 
    '/cart/remove' => [
        'POST' => ['controller' => 'CartController', 'method' => 'remove', 'middlewares' => ['auth']]
    ], 
    '/checkout' => [
        'GET' => ['controller' => 'CheckoutController', 'method' => 'index', 'middlewares' => ['auth']],
        'POST' => ['controller' => 'CheckoutController', 'method' => 'placeOrder', 'middlewares' => ['auth']]
    ], 
    '/orders' => [
        'GET' => ['controller' => 'ClientOrderController', 'method' => 'index', 'middlewares' => ['auth']],
    ], 
    '/orders/show' => [
        'GET' => ['controller' => 'ClientOrderController', 'method' => 'show', 'middlewares' => ['auth']],
    ], 
    '/orders/pay' => [
        'GET' => ['controller' => 'ClientOrderController', 'method' => 'payForm', 'middlewares' => ['auth']],
        'POST' => ['controller' => 'ClientOrderController', 'method' => 'pay', 'middlewares' => ['auth']],
    ], 
    '/orders/show/increment' => [
        'POST' => ['controller' => 'ClientOrderController', 'method' => 'increment', 'middlewares' => ['auth']],
    ], 
    '/orders/show/decrement' => [
        'POST' => ['controller' => 'ClientOrderController', 'method' => 'decrement', 'middlewares' => ['auth']],
    ], 
    '/orders/show/remove' => [
        'POST' => ['controller' => 'ClientOrderController', 'method' => 'remove', 'middlewares' => ['auth']],
    ], 
    '/orders/show/notes' => [
        'POST' => ['controller' => 'ClientOrderController', 'method' => 'updateNotes', 'middlewares' => ['auth']],
    ], 
    '/admin/orders' => [
        'GET' => ['controller' => 'AdminOrderController', 'method' => 'index', 'middlewares' => []],
    ],
    '/admin/orders/show' => [
        'GET' => ['controller' => 'AdminOrderController', 'method' => 'show', 'middlewares' => []],
    ],
    '/admin/orders/update' => [
        'POST' => ['controller' => 'AdminOrderController', 'method' => 'update', 'middlewares' => []],
    ],
    '/admin/orders/pay' => [
        'GET' => ['controller' => 'AdminOrderController', 'method' => 'payForm', 'middlewares' => []],
        'POST' => ['controller' => 'AdminOrderController', 'method' => 'pay', 'middlewares' => []],
    ],
    '/admin/orders/updatePaymentStatus' => [
        'POST' => ['controller' => 'AdminOrderController', 'method' => 'updatePaymentStatus', 'middlewares' => []],
    ],

];
