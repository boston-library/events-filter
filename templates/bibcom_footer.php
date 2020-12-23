<?php

$footer = <<<EOT
    <!-- Start BiblioCommons -->
    <div class="clear"></div>
    </div>
    <!-- .entry-content -->
    </article>
    <!-- #post -->
    </div>
    <!-- #content -->
    </div>
    <!-- #primary -->
    </div>
    <!-- content_wrap -->
    </div>
    <!-- .container_12 -->
    </div>
    <!-- #page -->
    </section>
    <!-- end biblioweb_container -->
    $template_parts->footer
    <!-- End BiblioCommons -->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/2.0.0/handlebars.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.7/dt-1.10.18/r-2.2.2/datatables.min.js"></script>
    $template_parts->js

    <script>
      $(document).ready(function(){
        $('#results').DataTable({
          responsive: true
        });
      });

      /**
       * Fix two scrollbars bug
      $(':not(html)').css('overflow', 'hidden');
       */
    </script>
    
  </body>
</html>
EOT;
echo $footer;
