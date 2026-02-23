<script>
	$(function() {ldelim}
		$('#schemaOrgSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form
	class="pkp_form"
	id="schemaOrgSettingsForm"
	method="post"
	action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}"
>
	{csrf}

	{fbvFormArea id="schemaOrgSettingsFormArea"}

		{fbvFormSection title="plugins.generic.schemaOrg.settings.schemaType" description="plugins.generic.schemaOrg.settings.schemaType.description"}

			<ul class="pkp_form_radio_list" style="list-style:none; padding:0; margin:0.5em 0;">
				<li style="margin-bottom:0.75em;">
					<label style="cursor:pointer;">
						<input
							type="radio"
							name="schemaType"
							value="ScholarlyArticle"
							{if $schemaType === 'ScholarlyArticle' || !$schemaType}checked="checked"{/if}
						/>
						<strong>ScholarlyArticle</strong>
						&mdash; {translate key="plugins.generic.schemaOrg.settings.scholarlyArticle.hint"}
					</label>
				</li>
				<li>
					<label style="cursor:pointer;">
						<input
							type="radio"
							name="schemaType"
							value="MedicalScholarlyArticle"
							{if $schemaType === 'MedicalScholarlyArticle'}checked="checked"{/if}
						/>
						<strong>MedicalScholarlyArticle</strong>
						&mdash; {translate key="plugins.generic.schemaOrg.settings.medicalScholarlyArticle.hint"}
					</label>
				</li>
			</ul>

		{/fbvFormSection}

	{/fbvFormArea}

	{fbvFormButtons submitText="common.save"}
</form>
