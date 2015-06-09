#!/usr/bin/perl

print "Content-Type: text/html\n\n";
print "<font color='blue'>\n";
print "<p><h3>It's a CGI script test</h3>\n";
print "</font>\n";

open (HTML,"../htdocs/test.html") or die "Can't open test.html: $!";
while (<HTML>) {
    print;
}

print "</body>\n";
print "</html>\n";
