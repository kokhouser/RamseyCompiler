// Dwayne Towell
//
// CS220
//
// Program to test an simple function implemented in 32-bit 
// 80x86 assembly language

#include <iostream>
#include <cassert>
using namespace std;

extern int f(int x,int y) asm("f");

int main()
{
    assert(f(-1,0) == 0);
    assert(f(-1,1) == 1);
    assert(f(-1,2) == 2);
    assert(f(-1,3) == 3);
    assert(f(-1,4) == 4);

    assert(f(0,10) == 7);
    assert(f(0,11) == 8);
    assert(f(0,12) == 9);
    assert(f(0,13) == 10);
    assert(f(0,14) == 11);

    assert(f(1,0) == -6);
    assert(f(1,1) == -5);
    assert(f(1,2) == -4);
    assert(f(1,3) == -3);
    assert(f(1,4) == -2);

    assert(f(2,0) == -9);
    assert(f(2,1) == -8);
    assert(f(2,2) == -7);
    assert(f(2,3) == -6);
    assert(f(2,4) == -5);
    assert(f(2,-1) == -10);
    assert(f(2,-2) == -11);

    assert(f(10,10) == -23);
    assert(f(10,-1) == -34);
    assert(f(100,303) == 0);

    cout << "you made it!\n";
}