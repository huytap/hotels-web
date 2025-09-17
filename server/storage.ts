import { 
  type User, 
  type InsertUser,
  type ContactSubmission,
  type InsertContactSubmission,
  type TrialRequest,
  type InsertTrialRequest
} from "@shared/schema";
import { randomUUID } from "crypto";

// modify the interface with any CRUD methods
// you might need

export interface IStorage {
  getUser(id: string): Promise<User | undefined>;
  getUserByUsername(username: string): Promise<User | undefined>;
  createUser(user: InsertUser): Promise<User>;
  
  // Contact submissions
  createContactSubmission(submission: InsertContactSubmission): Promise<ContactSubmission>;
  getContactSubmissions(): Promise<ContactSubmission[]>;
  getContactSubmission(id: string): Promise<ContactSubmission | undefined>;
  
  // Trial requests
  createTrialRequest(request: InsertTrialRequest): Promise<TrialRequest>;
  getTrialRequests(): Promise<TrialRequest[]>;
  getTrialRequest(id: string): Promise<TrialRequest | undefined>;
}

export class MemStorage implements IStorage {
  private users: Map<string, User>;
  private contactSubmissions: Map<string, ContactSubmission>;
  private trialRequests: Map<string, TrialRequest>;

  constructor() {
    this.users = new Map();
    this.contactSubmissions = new Map();
    this.trialRequests = new Map();
  }

  async getUser(id: string): Promise<User | undefined> {
    return this.users.get(id);
  }

  async getUserByUsername(username: string): Promise<User | undefined> {
    return Array.from(this.users.values()).find(
      (user) => user.username === username,
    );
  }

  async createUser(insertUser: InsertUser): Promise<User> {
    const id = randomUUID();
    const user: User = { ...insertUser, id };
    this.users.set(id, user);
    return user;
  }

  // Contact submissions
  async createContactSubmission(insertSubmission: InsertContactSubmission): Promise<ContactSubmission> {
    const id = randomUUID();
    const submission: ContactSubmission = { 
      ...insertSubmission, 
      id, 
      createdAt: new Date() 
    };
    this.contactSubmissions.set(id, submission);
    return submission;
  }

  async getContactSubmissions(): Promise<ContactSubmission[]> {
    return Array.from(this.contactSubmissions.values())
      .sort((a, b) => b.createdAt.getTime() - a.createdAt.getTime());
  }

  async getContactSubmission(id: string): Promise<ContactSubmission | undefined> {
    return this.contactSubmissions.get(id);
  }

  // Trial requests
  async createTrialRequest(insertRequest: InsertTrialRequest): Promise<TrialRequest> {
    const id = randomUUID();
    const request: TrialRequest = { 
      ...insertRequest,
      id, 
      createdAt: new Date(),
      company: insertRequest.company ?? null,
      budget: insertRequest.budget ?? null,
      description: insertRequest.description ?? null
    };
    this.trialRequests.set(id, request);
    return request;
  }

  async getTrialRequests(): Promise<TrialRequest[]> {
    return Array.from(this.trialRequests.values())
      .sort((a, b) => b.createdAt.getTime() - a.createdAt.getTime());
  }

  async getTrialRequest(id: string): Promise<TrialRequest | undefined> {
    return this.trialRequests.get(id);
  }
}

export const storage = new MemStorage();
