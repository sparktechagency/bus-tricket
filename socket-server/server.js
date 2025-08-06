import { createServer } from "http";
import express from "express";
import { Server } from "socket.io";

const app = express();
const server = createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*", // DEV mode only
    },
});


const users = new Map();

io.on("connection", (socket) => {
    // console.log(`New socket connected: ${socket.id}`);

    socket.on("join", ({ userId }) => {
        users.set(userId, socket.id); // store userId -> socketId
        // console.log(`User connected: ${userId} => ${socket.id}`);

        socket.emit("join", {
            userId,
            users: Object.fromEntries(users), // Optional
        });
    });

    socket.on("message", ({ receiverId, message }) => {
        const receiverSocketId = users.get(receiverId);

        if (receiverSocketId) {
            // console.log(`Sending private message to ${receiverId} (${receiverSocketId})`);

            io.to(receiverSocketId).emit("message", {
                from: socket.id,
                message,
            });
        } else {
            // console.log(`Receiver ${receiverId} not found or offline.`);
        }
    });

    
    socket.on("disconnect", () => {
        let disconnectedUserId = null;

        for (const [userId, sockId] of users.entries()) {
            if (sockId === socket.id) {
                disconnectedUserId = userId;
                users.delete(userId);
                break;
            }
        }

        if (disconnectedUserId) {
            // console.log(`User disconnected: ${disconnectedUserId} (${socket.id})`);
            socket.broadcast.emit("user-disconnected", { userId: disconnectedUserId });
        }
    });
});

const PORT = process.env.PORT || 3050;
server.listen(PORT, "0.0.0.0", () => {
    // console.log(`Socket server running at http://localhost:${PORT}`);
});
