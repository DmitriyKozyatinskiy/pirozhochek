<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Woocommerce;
use Automattic\WooCommerce\HttpClient\HttpClientException;

set_time_limit(0);

class ProductsController extends Controller
{
  public function __construct()
  {
    set_time_limit(0);
    $data = ['per_page' => 100];
    $this->categories = array_merge(
      Woocommerce::get('products/categories', $data)
    );
    $this->categories = \collect($this->categories);
    $this->attributes = \collect(Woocommerce::get('products/attributes'));
  }

  public function showUploadForm()
  {
    return view('products/upload');
  }

  protected function upload(Request $request)
  {
    $this->validate($request, [
      'type' => 'required',
      'products' => 'required|file',
    ]);
    $type = $request->type;
    $path = $request->products->path();
    $amount = 0;
    \Excel::load($path, function ($reader) use ($type, &$amount) {
      $results = \collect($reader->get());
      $grouped = $results->groupBy(function ($item) {
        return $item['zgrupovaniy_tovar'];
      });
      $amount = count($grouped);
      $grouped->each(function ($groupedProducts, $key) use ($type) {
        if (!$key) {
          return;
        }
        $children = [];
        \collect($groupedProducts)->each(function ($value) use (&$children, &$amount, $type) {
          $amount = $amount + 1;
          $childProduct = $type === 'cats'
            ? $this->createCatsProduct($value)
            : $this->createDogsProduct($value);
          $child = Woocommerce::post('products', $childProduct);
          array_push($children, $child['id']);
        });

        $groupedProduct = $type === 'cats'
          ? $this->createGroupedCatsProduct($groupedProducts, $children)
          : $groupedProduct = $this->createGroupedDogsProduct($groupedProducts, $children);
        $parent = Woocommerce::post('products', $groupedProduct);
        try {
          Woocommerce::post('products/set_grouped', [
            'parent' => $parent,
            'children' => $children,
          ]);
        } catch (HttpClientException $e) {
        }
      });
    })->get();
    return redirect()->route('products/upload')
      ->with('products-upload-success', $amount . ' товаров загружено!');
  }

  private function getExcelAttributeName($attributeName)
  {
    switch ($attributeName) {
      case 'Вік котика':
        return 'vik_kotika';
      case 'Котячий Бренд':
        return 'kotyachiy_brend';
      case 'Порода котика':
        return 'poroda_kotika';
      case 'Лікувальне призначення':
        return 'likuvalne_priznachennya';
      case 'Спеціальне призначення':
        return 'spetsialne_priznachennya';
      case 'Країна-виробник':
        return 'kraina_virobnik';
      case 'Вага упаковки':
        return 'vaga_upakovki';
      case 'Розмір упаковки':
        return 'rozmir_upakovki';

      case 'Вік песика':
        return 'vik_pesika';
      case 'Собачий Бренд':
        return 'sobachiy_brend';
      case 'Порода собаки':
        return 'poroda_sobaki';
      case 'Розмір собаки':
        return 'rozmir_sobaki';

      default:
        return false;
    }
  }

  private function getCategory($categoryName, $type)
  {
    try {
      $category = $this->categories->filter(function ($value, $key) use ($categoryName, $type) {
        if ($categoryName === 'Котикам' || $categoryName === 'Песикам') {
          return $value['name'] === $categoryName;
        } else if ($type === 'dog') {
          return $value['name'] === $categoryName && preg_match('/(-dog)$/i', $value['slug']);
        } else if ($type === 'cat') {
          return $value['name'] === $categoryName && preg_match('/(-cat)$/i', $value['slug']);
        } else {
          return $value['name'] === $categoryName;
        }
      })->first();


      if (!$category) {
        throw new Exception('Category "' . $categoryName . '" not found');
      }

      return [
        'id' => $category['id'],
        'name' => $category['name'],
        'slug' => $category['slug'],
      ];
    } catch (Exception $e) {
      throw new Exception('Category ' . $categoryName . ' not found');
    }
  }


  private function getTerms($attributeName, $product, $isGrouped)
  {
    $options = [];
    if ($isGrouped && ($attributeName === 'vaga_upakovki' || $attributeName === 'rozmir_upakovki')) {
      $product->each(function ($value, $key) use ($attributeName, &$options) {
        if ($value[$attributeName] && $value[$attributeName] !== '-') {
          array_push($options, $value[$attributeName]);
        }
      });
    } else {
      if ($isGrouped) {
        $productData = $product[0];
      } else {
        $productData = $product;
      }
      if ($productData[$attributeName] !== '-') {
        $splittedOptions = explode('|', $productData[$attributeName]);
        \collect($splittedOptions)->each(function ($value, $key) use (&$options) {
          array_push($options, $value);
        });
      }
    }
    return $options;
  }

  private function getAttribute($attributeName, $product, $isGrouped, $type = 'global', $isVisible = true)
  {
    try {
      $attribute = $this->attributes->filter(function ($value, $key) use ($attributeName, $type) {
        if ($type === 'dog') {
          return $value['name'] === $attributeName && preg_match('/(-dog)$/i', $value['slug']);
        } else if ($type === 'cat') {
          return $value['name'] === $attributeName && preg_match('/(-cat)$/i', $value['slug']);
        } else {
          return $value['name'] === $attributeName;
        }
      })->first();

      if (!$attribute) {
        throw new Exception('Attribute "' . $attributeName . '" not found');
      }

      $excelAttributeName = $this->getExcelAttributeName($attributeName);
      if (($isGrouped && $product[0][$excelAttributeName] === '-')
        || (!$isGrouped && $product[$excelAttributeName] === '-')
      ) {
        return false;
      }
      $options = $this->getTerms($excelAttributeName, $product, $isGrouped);

      return [
        'id' => $attribute['id'],
        'name' => $attribute['name'],
        'variation' => false,
        'visible' => $isVisible,
        'options' => $options,
      ];
    } catch (Exception $e) {
      throw new Exception('Attribute ' . $attributeName . ' not found');
    }
  }


  private function createGroupedCatsProduct($groupedProducts, $children)
  {
    $data = $groupedProducts[0];
    $catsCategory = $this->getCategory('Котикам', 'cat');
    $productCategory = $this->getCategory($data->kategoriya, 'cat');

    $attributes = [];
    $catAgeAttribute = $this->getAttribute('Вік котика', $groupedProducts, true, 'cat', true);
    $catBrand = $this->getAttribute('Котячий Бренд', $groupedProducts, true, 'cat', true);
    $catBreed = $this->getAttribute('Порода котика', $groupedProducts, true, 'cat', true);
    $catHeal = $this->getAttribute('Лікувальне призначення', $groupedProducts, true, 'cat', true);
    $catSpecial = $this->getAttribute('Спеціальне призначення', $groupedProducts, true, 'cat', true);

    $country = $this->getAttribute('Країна-виробник', $groupedProducts, true, 'global', true);
    $weight = $this->getAttribute('Вага упаковки', $groupedProducts, true, 'global', true);
    $weightClass = $this->getAttribute('Розмір упаковки', $groupedProducts, true, 'global', false);

    array_push($attributes, $catAgeAttribute, $catBrand,
      $catBreed, $catHeal, $catSpecial, $country, $weight, $weightClass);
    $attributes = \collect($attributes)->filter(function ($value) {
      return $value;
    });
    if ($data->ssylka_izobrazheniya) {
      $images = [
        [
          'id' => $data->ssylka_izobrazheniya,
          'position' => 0,
        ]
      ];
    } else {
      $images = [];
    }

    $result = [
      'name' => $data->zgrupovaniy_tovar,
      'slug' => strtolower(preg_replace('/\s+/', '-', $data->zgrupovaniy_tovar)),
      'type' => 'grouped',
      'status' => 'publish',
      'catalog_visibility' => 'visible',
      'featured' => false,
      'sku' => (string)$data->artikul_grupi,
      'price' => $data->price,
      'regular_price' => '',
      'sale_price' => '',
      'tax_status' => 'taxable',
      'reviews_allowed' => false,
      'parent_id' => 0,
      'grouped_products' => $children,
      'categories' => [
        $catsCategory,
        $productCategory,
      ],
      'images' => $images,
      'attributes' => $attributes,
      'children' => $children,
    ];

    return $result;
  }

  private function createCatsProduct($product)
  {
    $data = $product;
    $catsCategory = $this->getCategory('Котикам', 'cat');
    $productCategory = $this->getCategory($data->kategoriya, 'cat');

    $attributes = [];
    $catAgeAttribute = $this->getAttribute('Вік котика', $data, false, 'cat', true);
    $catBrand = $this->getAttribute('Котячий Бренд', $data, false, 'cat', true);
    $catBreed = $this->getAttribute('Порода котика', $data, false, 'cat', true);
    $catHeal = $this->getAttribute('Лікувальне призначення', $data, false, 'cat', true);
    $catSpecial = $this->getAttribute('Спеціальне призначення', $data, false, 'cat', true);

    $country = $this->getAttribute('Країна-виробник', $data, false, 'global', true);
    $weight = $this->getAttribute('Вага упаковки', $data, false, 'global', true);
    $weightClass = $this->getAttribute('Розмір упаковки', $data, false, 'global', false);

    array_push($attributes, $catAgeAttribute, $catBrand,
      $catBreed, $catHeal, $catSpecial, $country, $weight, $weightClass);
    $attributes = \collect($attributes)->filter(function ($value) {
      return $value;
    });
    if ($data->ssylka_izobrazheniya) {
      $images = [
        [
          'id' => $data->ssylka_izobrazheniya,
          'position' => 0,
        ]
      ];
    } else {
      $images = [];
    }

    $result = [
      'name' => $data->zvichayniy_tovar,
      'slug' => strtolower(preg_replace('/\s+/', '-', $data->zgrupovaniy_tovar) . '-' . explode(' ', $data->rozmir_upakovki)[0]),
      'type' => 'simple',
      'status' => 'publish',
      'catalog_visibility' => 'hidden',
      'featured' => false,
      'sku' => (string)$data->artikul_tovara,
      'price' => (string)$data->price,
      'regular_price' => (string)$data->price,
      'sale_price' => '',
      'tax_status' => 'taxable',
      'reviews_allowed' => false,
      'categories' => [
        $catsCategory,
        $productCategory,
      ],
      'images' => $images,
      'attributes' => $attributes,
    ];

    return $result;
  }


  private function createGroupedDogsProduct($groupedProducts, $children)
  {
    $data = $groupedProducts[0];
    $catsCategory = $this->getCategory('Песикам', 'dog');
    $productCategory = $this->getCategory($data->kategoriya, 'dog');

    $attributes = [];
    $catAgeAttribute = $this->getAttribute('Вік песика', $groupedProducts, true, 'dog', true);
    $catBrand = $this->getAttribute('Собачий Бренд', $groupedProducts, true, 'dog', true);
    $catBreed = $this->getAttribute('Порода собаки', $groupedProducts, true, 'dog', true);
    $catHeal = $this->getAttribute('Лікувальне призначення', $groupedProducts, true, 'dog', true);
    $catSize = $this->getAttribute('Розмір собаки', $groupedProducts, true, 'dog', true);
    $catSpecial = $this->getAttribute('Спеціальне призначення', $groupedProducts, true, 'dog', true);

    $country = $this->getAttribute('Країна-виробник', $groupedProducts, true, 'global', true);
    $weight = $this->getAttribute('Вага упаковки', $groupedProducts, true, 'global', true);
    $weightClass = $this->getAttribute('Розмір упаковки', $groupedProducts, true, 'global', false);

    array_push($attributes, $catAgeAttribute, $catBrand,
      $catBreed, $catHeal, $catSize, $catSpecial, $country, $weight, $weightClass);
    $attributes = \collect($attributes)->filter(function ($value) {
      return $value;
    });
    if ($data->ssylka_izobrazheniya) {
      $images = [
        [
          'id' => $data->ssylka_izobrazheniya,
          'position' => 0,
        ]
      ];
    } else {
      $images = [];
    }

    $result = [
      'name' => $data->zgrupovaniy_tovar,
      'slug' => strtolower(preg_replace('/\s+/', '-', $data->zgrupovaniy_tovar)),
      'type' => 'grouped',
      'status' => 'publish',
      'catalog_visibility' => 'visible',
      'featured' => false,
      // 'description' => $data->detalnoe_opisanie,
      // 'short_description' => $data->kratkoe_opisanie,
      'sku' => (string)$data->artikul_grupi,
      'price' => $data->price,
      'regular_price' => '',
      'sale_price' => '',
      'tax_status' => 'taxable',
      'reviews_allowed' => false,
      'parent_id' => 0,
      'grouped_products' => $children,
      'categories' => [
        $catsCategory,
        $productCategory,
      ],
      'images' => $images,
      'attributes' => $attributes,
      'children' => $children,
    ];

    return $result;
  }

  private function createDogsProduct($product)
  {
    $data = $product;
    $catsCategory = $this->getCategory('Песикам', 'dog');
    $productCategory = $this->getCategory($data->kategoriya, 'dog');

    $attributes = [];
    $catAgeAttribute = $this->getAttribute('Вік песика', $data, false, 'dog', true);
    $catBrand = $this->getAttribute('Собачий Бренд', $data, false, 'dog', true);
    $catBreed = $this->getAttribute('Порода собаки', $data, false, 'dog', true);
    $catHeal = $this->getAttribute('Лікувальне призначення', $data, false, 'dog', true);
    $catSize = $this->getAttribute('Розмір собаки', $data, false, 'dog', true);
    $catSpecial = $this->getAttribute('Спеціальне призначення', $data, false, 'dog', true);

    $country = $this->getAttribute('Країна-виробник', $data, false, 'global', true);
    $weight = $this->getAttribute('Вага упаковки', $data, false, 'global', true);
    $weightClass = $this->getAttribute('Розмір упаковки', $data, false, 'global', false);

    array_push($attributes, $catAgeAttribute, $catBrand,
      $catBreed, $catHeal, $catSize, $catSpecial, $country, $weight, $weightClass);
    $attributes = \collect($attributes)->filter(function ($value) {
      return $value;
    });
    if ($data->ssylka_izobrazheniya) {
      $images = [
        [
          'id' => $data->ssylka_izobrazheniya,
          'position' => 0,
        ]
      ];
    } else {
      $images = [];
    }

    $result = [
      'name' => $data->zvichayniy_tovar,
      'slug' => strtolower(preg_replace('/\s+/', '-', $data->zgrupovaniy_tovar) . '-' . explode(' ', $data->rozmir_upakovki)[0]),
      'type' => 'simple',
      'status' => 'publish',
      'catalog_visibility' => 'hidden',
      'featured' => false,
      'sku' => (string)$data->artikul_tovara,
      'price' => (string)$data->price,
      'regular_price' => (string)$data->price,
      'sale_price' => '',
      'tax_status' => 'taxable',
      'reviews_allowed' => false,
      'categories' => [
        $catsCategory,
        $productCategory,
      ],
      'images' => $images,
      'attributes' => $attributes,
    ];

    return $result;
  }
}