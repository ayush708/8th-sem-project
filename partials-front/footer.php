<?php
class FrontFooterRenderer {
    public function render() {
        ?>
    <!--Footer section start-->
    <section class="footer">
        <div class="container">
            <p>BCA 6th sem Project by Ayush</p>
        </div>
    </section>
    <!--Footer section end-->

</body>
</html>
<?php
    }
}

$frontFooterRenderer = new FrontFooterRenderer();
$frontFooterRenderer->render();
?>