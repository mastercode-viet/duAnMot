<?php 
class DashboardController{
    public function dashboard(){
        $categoryModel = new CategoryUserModel();
        $listCategory =$categoryModel->getCategoryDashboard();

        $productModel = new ProductUserModel();
        $listProduct =$productModel->getProductDashboard();
      
        include 'app/Views/Users/index.php';
    }
    public function myAccount(){
        include 'app/Views/Users/myaccount.php';
    }
    public function accountDetail(){
        $userModel = new UserModel2();
        $user = $userModel->getCurrentUser();
        include 'app/Views/Users/account-detail.php';
    }
       public function accountUpdate(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->changePassword();
            $userModel = new UserModel2();
            $user= $userModel->getCurrentUser();

            $uploadDir  = 'assets/Admin/upload/';
            $allowTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
            $deshPath   = "";
            if (! empty($_FILES['image']['name'])) {
                $fileTmpPath   = $_FILES['image']['tmp_name'];
                $fileType      = mime_content_type($fileTmpPath);
                $fileName      = basename($_FILES['image']['name']);
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName   = uniqid() . "." . $fileExtension;

                if (in_array($fileType, $allowTypes)) {
                    $deshPath = $uploadDir . $newFileName;
                
                    // **Xóa ảnh cũ trước khi tải ảnh mới**
                    if (!empty($user->image) && file_exists($user->image)) {
                        unlink($user->image);
                    }
                
                    if (!move_uploaded_file($fileTmpPath, $deshPath)) {
                        $_SESSION['message'] = "Không thể tải ảnh lên. Vui lòng thử lại.";
                        header("Location: " . BASE_URL . "?role=admin&act=add-user");
                        exit;
                    }
                } else {
                    $_SESSION['message'] = "Định dạng file không hợp lệ. Vui lòng chọn file ảnh.";
                    header("Location: " . BASE_URL . "?role=admin&act=add-user");
                    exit;
                }
            }

            $userModel = new UserModel2();
        
                $message = $userModel->updateCurrentUser($deshPath);

                if ($message) {
                    $_SESSION['message'] = 'Chỉnh sửa thành công';
                    header("Location: " . BASE_URL . "?act=account-detail");
                    exit;
                } else {
                    $_SESSION['message'] = 'Chỉnh sửa không thành công';
                    header("Location: " . BASE_URL . "?act=account-detail");
                    exit;
                }
                
        
         
            
        }
       }
       public function changePassword(){
        if(
            $_POST['current-password'] != "" &&

         $_POST['new-password'] != "" &&
         $_POST['confirm-password'] != "" &&
         $_POST['new-password']==$_POST['confirm-password']
        ){
            $userModel = new UserModel2();
            $userModel->changePassword();
        }
       }
     
       public function showshop(){
        $productModel = new ProductUserModel();
        $categoryModel = new CategoryUserModel();
        $listProduct = $productModel->getDataShop();
        if(isset($_GET['category_id'])){
            $category = $categoryModel->getCategoryById($_GET['category_id']);

        }
        $listCategory = $categoryModel->getCategoryDashboard();
        $stock = $productModel->getProductStook();

        if(isset($_GET['instock'])){
          $listProduct = array_filter($listProduct, function($product){
              return $product->stock > 0;
          });
        }
        if(isset($_GET['outstock'])){
          $listProduct = array_filter($listProduct, function($product){
              return $product->stock == 0;
          });
        }

        if(isset($_GET['min'])){
            $listProduct = array_filter($listProduct, function($product){
                if($product->price_sale != null){
                    return $product->price_sale > $_GET['min'];
                }
                return $product->price > $_GET['min'];
            });
        }
        if(isset($_GET['max'])){
            $listProduct = array_filter($listProduct, function($product){
                if($product->price_sale != null){
                    return $product->price_sale < $_GET['max'];
                }
                return $product->price < $_GET['max'];
            });
        }
        if(isset($_GET['product-name'])){
            $listProduct = $productModel->searchProduct();
        }
        include 'app/Views/Users/shop.php';
        
  
}
   public function productDetail(){
    $productModel = new ProductUserModel();
    $product = $productModel->getProductById();
    $productImage = $productModel->getProductImageById();
    $otherProduct = $productModel->getOtherProduct($product->category_id, $product->id);

    $comment = $productModel->getComment($product->id);
    foreach ($comment as $key => $value) {
        $rating = $productModel->getCommentByUser($product->id,$value->user_id);
        if($rating){
            $comment[$key]->rating = $rating->rating;
         
    }
    else{
        $comment[$key]->rating = null;
    }
   
}
  $ratingProduct = $productModel->getRating($product->id);
include 'app/Views/Users/product-detail.php';
   }

 public function writeReview(){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $productModel = new ProductUserModel();
        $productModel->writeReviews();
        $productModel->saveComment();

    }
    header("Location: " . BASE_URL . "?act=product-detail&product_id=".$_POST['productId']);
 }
}