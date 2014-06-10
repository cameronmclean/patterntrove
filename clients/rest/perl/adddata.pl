#!/bin/perl

use LWP::UserAgent;
use HTTP::Request::Common;

## Example on how to use the rest api uploading files with a multipart/form-data submission


my $userAgent = LWP::UserAgent->new;

my $uid = "#####################";
my $sub_url = "http://example.labtrove.org/api/rest/adddata/uid/$uid";

my $request = <<END
<?xml version="1.0" encoding="UTF-8"?>
<dataset>        
	<title>Test File</title>        
  	<data>  
		<dataitem type="file" ext="png" main="1" filename="out.png">file_0</dataitem>
	</data> 
</dataset>
END
;

my $response = $userAgent->request(POST "$sub_url", Content_Type => 'multipart/form-data', Content => [file_0 => ['out.png'],request=>"$request" ]);

print $response->as_string


