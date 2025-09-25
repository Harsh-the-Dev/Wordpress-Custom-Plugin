#include <iostream>
#include <fcntl.h>
#include <termios.h>
#include <unistd.h>
#include <string.h>

int main() {
    const char* port = "/dev/ttyUSB0";   // Change to your port (e.g., /dev/ttySCANNER_ENTER)
    int fd = open(port, O_RDWR | O_NOCTTY | O_SYNC);

    if (fd < 0) {
        std::cerr << "Error opening serial port " << port << std::endl;
        return 1;
    }

    // Configure port
    struct termios tty;
    memset(&tty, 0, sizeof tty);

    if (tcgetattr(fd, &tty) != 0) {
        std::cerr << "Error from tcgetattr" << std::endl;
        return 1;
    }

    cfsetospeed(&tty, B9600); // Set baud rate to 9600
    cfsetispeed(&tty, B9600);

    tty.c_cflag = (tty.c_cflag & ~CSIZE) | CS8;     // 8-bit chars
    tty.c_iflag &= ~IGNBRK;                         // disable break processing
    tty.c_lflag = 0;                                // no signaling chars, no echo
    tty.c_oflag = 0;                                // no remapping, no delays
    tty.c_cc[VMIN]  = 1;                            // read blocks until 1 char
    tty.c_cc[VTIME] = 5;                            // 0.5 seconds read timeout

    tty.c_iflag &= ~(IXON | IXOFF | IXANY);         // shut off xon/xoff ctrl
    tty.c_cflag |= (CLOCAL | CREAD);                // enable receiver
    tty.c_cflag &= ~(PARENB | PARODD);              // no parity
    tty.c_cflag &= ~CSTOPB;                         // one stop bit
    tty.c_cflag &= ~CRTSCTS;                        // no flow control

    if (tcsetattr(fd, TCSANOW, &tty) != 0) {
        std::cerr << "Error from tcsetattr" << std::endl;
        return 1;
    }

    // Write data
    const char* msg = "Hello from Raspberry Pi!\\n";
    int written = write(fd, msg, strlen(msg));
    if (written < 0) {
        std::cerr << "Error writing to serial port" << std::endl;
    } else {
        std::cout << "Wrote " << written << " bytes: " << msg << std::endl;
    }

    // Read data
    char buf[100];
    int n = read(fd, buf, sizeof(buf));
    if (n > 0) {
        buf[n] = '\\0'; // Null-terminate
        std::cout << "Read: " << buf << std::endl;
    } else {
        std::cerr << "No data read" << std::endl;
    }

    close(fd);
    return 0;
}
