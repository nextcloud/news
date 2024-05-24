{ pkgs ? import <nixpkgs> {} }:
  pkgs.mkShell {
    nativeBuildInputs = with pkgs; [
      gnumake
      nodejs
      php83
      php83Packages.composer
      zellij # smart terminal workspace
      lazygit # git terminal
    ];
}
