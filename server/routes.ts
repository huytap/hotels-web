import type { Express } from "express";
import { createServer, type Server } from "http";
import { storage } from "./storage";
import { insertContactSubmissionSchema, insertTrialRequestSchema } from "@shared/schema";
import { z } from "zod";

export async function registerRoutes(app: Express): Promise<Server> {
  // Contact form submission endpoint
  app.post("/api/contact", async (req, res) => {
    try {
      // Validate request body
      const validatedData = insertContactSubmissionSchema.parse(req.body);
      
      // Create contact submission
      const submission = await storage.createContactSubmission(validatedData);
      
      res.status(201).json({
        success: true,
        message: "Contact form submitted successfully",
        id: submission.id
      });
    } catch (error) {
      if (error instanceof z.ZodError) {
        res.status(400).json({
          success: false,
          message: "Invalid form data",
          errors: error.errors
        });
      } else {
        console.error("Contact form submission error:", error);
        res.status(500).json({
          success: false,
          message: "Internal server error"
        });
      }
    }
  });

  // Trial request submission endpoint
  app.post("/api/trial", async (req, res) => {
    try {
      // Validate request body
      const validatedData = insertTrialRequestSchema.parse(req.body);
      
      // Create trial request
      const request = await storage.createTrialRequest(validatedData);
      
      res.status(201).json({
        success: true,
        message: "Trial request submitted successfully",
        id: request.id
      });
    } catch (error) {
      if (error instanceof z.ZodError) {
        res.status(400).json({
          success: false,
          message: "Invalid form data",
          errors: error.errors
        });
      } else {
        console.error("Trial request submission error:", error);
        res.status(500).json({
          success: false,
          message: "Internal server error"
        });
      }
    }
  });

  // Get contact submissions (admin only - for debugging in development)
  app.get("/api/contact", async (req, res) => {
    try {
      // Only allow in development environment
      if (process.env.NODE_ENV === 'production') {
        return res.status(404).json({
          success: false,
          message: "Not found"
        });
      }
      
      const submissions = await storage.getContactSubmissions();
      res.json({ submissions });
    } catch (error) {
      console.error("Get contact submissions error:", error);
      res.status(500).json({
        success: false,
        message: "Internal server error"
      });
    }
  });

  // Get trial requests (admin only - for debugging in development)
  app.get("/api/trial", async (req, res) => {
    try {
      // Only allow in development environment
      if (process.env.NODE_ENV === 'production') {
        return res.status(404).json({
          success: false,
          message: "Not found"
        });
      }
      
      const requests = await storage.getTrialRequests();
      res.json({ requests });
    } catch (error) {
      console.error("Get trial requests error:", error);
      res.status(500).json({
        success: false,
        message: "Internal server error"
      });
    }
  });

  const httpServer = createServer(app);

  return httpServer;
}
