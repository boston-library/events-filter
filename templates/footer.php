</div>

</div>
	</div>
</div>
	</div>
</div>
	</div>
		</div>
	</div>
</div>
</div>                <div class="clear"></div>
            </div>
        </article>

        <div class="clear"></div>
    </div><!-- #page -->
</section><!-- end biblioweb_container -->
</div>
<?php echo $template_parts->footer; ?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js">
	</script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/2.0.0/handlebars.min.js">
	</script>
<?php echo $template_parts->js; ?>
<!-- LibCal code -->
<script src="https://api3.libcal.com/js/equipment.min.js"></script>
<script>

jQuery(function(){
	$("#libcal-select").change(function () {
		var selectedLoc = $(this).children("option:selected").val();
		if (selectedLoc != 0) {
			$("#libcal-submit").LibCalEquipmentBooking({
				iid: 3119, 
				gid: selectedLoc, 
				eid: 0, 
				width: 560, 
				height: 680
			});
		}
	  }).change();
	$("#libcal-form").submit(function(){
	  return false;
	});
});
</script>
<!-- end LibCal -->
	</body>
	</html>
