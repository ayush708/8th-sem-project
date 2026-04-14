<?php
class AdminFooterRenderer {
    public function render() {
        ?>
<html>
<!--footer section starts here-->
<div class="footer">
    <div class="wrapper">
        <p class="text-center">BCA 6th Sem Project by Ayush</p>
    </div>
</div>

<!-- footer section ends here-->
</body>
</html>
<?php
    }
}

$adminFooterRenderer = new AdminFooterRenderer();
$adminFooterRenderer->render();
?>