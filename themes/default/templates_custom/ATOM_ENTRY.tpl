	<entry>
		<title type="html">{TITLE}</title>
		<link rel="alternate" href="{VIEW_URL*}" />
		<id>{VIEW_URL*}</id>

		{$,The Salmon link points to a script in data_custom for handling any  }
		{$,"replies" (eg. comments) posted from downstream. In order to attach }
		{$,such comments to the right resource, we must have the ID and type,  }
		{$,which in this case is {ID} and the "mode" GET parameter (which may  }
		{$,not be completely reliable, but should be good enough in most cases)}
		{+START,IF,{$SALMON_ENABLED,{$_GET|,mode},{ID&}}}
			<link rel="salmon" href="{$BASE_URL}/data_custom/salmon.php?id={ID&}&amp;type={$_GET|,mode}"/>
		{+END}
		
		{+START,IF_NON_EMPTY,{DATE}}
			<published>{DATE*}</published>
		{+END}
		{+START,IF_NON_EMPTY,{EDIT_DATE}}
			<updated>{EDIT_DATE*}</updated>
		{+END}
		{+START,IF_EMPTY,{EDIT_DATE}}
			<updated>{DATE*}</updated>
		{+END}
		{+START,IF_NON_EMPTY,{CATEGORY}}
			<category term="{CATEGORY_RAW*}" label="{CATEGORY*}" />
		{+END}
		{+START,IF_NON_EMPTY,{AUTHOR}}
			<author>
				<name>{AUTHOR*}</name>
			</author>
		{+END}
		{+START,IF_NON_EMPTY,{SUMMARY}}
			<summary type="html">{SUMMARY}</summary>
		{+END}
		{+START,IF_NON_EMPTY,{NEWS}}
			<content type="html">{NEWS}</content>
		{+END}
		{+START,IF_PASSED,ENCLOSURE_URL}{+START,IF_PASSED,ENCLOSURE_LENGTH}{+START,IF_PASSED,ENCLOSURE_TYPE}
			<link length="{ENCLOSURE_LENGTH*}" type="{ENCLOSURE_TYPE*}" rel="enclosure">{ENCLOSURE_URL*}</link>
		{+END}{+END}{+END}
	</entry>

