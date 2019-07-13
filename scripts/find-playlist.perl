#!/usr/bin/perl
## /usr/bin/perl find-playlist.perl <bluray root dir>
#
# This script will find the playlist that has matching mpls and clipinfo segments.
# The one with the longest number of segments is typically the correct main movie file.
#
# Example:
#
# Create a movie directory (e.g. RED2) and copy BDMV/CLIPINF and BDMV/PLAYLIST to it.
# You should now have a structure like this:
#
#    RED2/BDMV/CLIPINF
#    RED2/BDMV/PLAYLIST
#
# Then execute the script:
#
# /usr/bin/perl find-playlist.perl RED2
#
#

my $path = shift;

my $clips     = read_m2ts("$path/BDMV/CLIPINF", ".clpi");
my $playlists = read_m2ts("$path/BDMV/PLAYLIST", ".mpls");

foreach my $list (sort {$a <=> $b} keys %{$playlists}) {
  my @clp = @{$playlists->{$list}};

  my @playlist = ();

  my $next = $clp[0];
  push(@playlist, $next);
  my $last = $next;
  $next = ${$clips->{$last}}[0];

  if($next && $next != $last) {
    while ($next && $next != $last) {
      push(@playlist, $next);
      $last = $next;
      $next = ${$clips->{$last}}[0];
    }
  }

  my $mpls = join(",",@clp);
  my $cliplist = join(",",@playlist);

  if( $mpls eq $cliplist ) {
    printf("$list :\n");
    printf("  mpls     -> $mpls\n");
    printf("  clipinfo -> $cliplist\n");
  }

}

sub read_m2ts {

  my $dir = shift;
  my $ext = shift;

  my $out = {};

  opendir(DIR, "$dir") || die "can't open $dir";
  my @dir = readdir(DIR);
  closedir DIR;

  foreach my $file ( grep{ /$ext$/ } @dir ) {

    open(XIN, "<$dir/$file");
    my @infile = <XIN>;
    chomp @infile;


    my $tmp = join(" ", @infile);
    my @stuff = $tmp=~m/\d\d\d\d\dM2TS/g;
    my @streams = ();
    foreach my $strm (@stuff) {
      my $a = $strm;
      $a =~s/^0*//;
      $a =~s/M2TS$//;
      push(@streams, $a);
    }

    my $fnum = $file;
    $fnum =~s/$ext$//;
    $fnum =~s/^0*//;
    $out->{$fnum}=[@streams];

  }

  return $out;

}

