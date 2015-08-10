<?php
namespace spec\PlasmaConduit;
use PHPSpec2\ObjectBehavior;

class Path extends ObjectBehavior {

    public function it_should_concat_two_peices_for_join() {
        Path::join("wat", "lol")->shouldReturn("wat/lol");
    }

    public function it_should_drop_excess_slashes_for_join() {
        Path::join("/a", "///b")->shouldReturn("/a/b");
    }

    public function it_should_correctly_traverse_for_join() {
        Path::join("/a", "b", "c", "..", "d")->shouldReturn("/a/b/d");
    }

    public function it_should_filter_empty_peices_for_join() {
        Path::join("", "lonely")->shouldReturn("lonely");
    }

    public function it_should_correctly_traverse_for_normalize() {
        Path::normalize("/a/b/c/../d")->shouldReturn("/a/b/d");
    }

    public function it_should_do_multiple_traversals_for_normalize() {
        Path::normalize("/a/b/c/../../d")->shouldReturn("/a/d");
    }

    public function it_should_drop_trailing_extra_slashes_for_normalize() {
        Path::normalize("/b/wat//")->shouldReturn("/b/wat/");
    }

    public function it_should_drop_extra_slashes_for_normalize() {
        Path::normalize("/b///wat/")->shouldReturn("/b/wat/");
    }

    public function it_should_resolve_empty_as_dot() {
        Path::normalize("")->shouldReturn(".");
    }

    public function it_should_return_slash_for_slash() {
        Path::normalize("/")->shouldReturn("/");
    }

}
