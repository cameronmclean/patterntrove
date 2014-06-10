import uk.ac.cam.ch.wwmm.oscar.Oscar;
import uk.ac.cam.ch.wwmm.oscar.chemnamedict.entities.*;
import java.util.*;
import java.io.*;
import java.net.ServerSocket;
import java.net.Socket;

public class QueryOscarServer implements Runnable{
	Socket clientSocket;
	static Oscar oscar;

	QueryOscarServer(Socket clientSocket) {
		this.clientSocket = clientSocket;
	}

	public static void main(String[] args) throws Exception {
		oscar = new Oscar();
		List<ResolvedNamedEntity> firstEntities = oscar.findAndResolveNamedEntities("Nitrous Oxide with make you laugh");
		String firstKeyword = firstEntities.get(0).getSurface();
		ServerSocket serverSocket = new ServerSocket(5642);
		System.out.println("Listening");
		while (true) {
			Socket socket = serverSocket.accept();
			System.out.println("Connected");
			new Thread(new QueryOscarServer(socket)).start();
		}
	}
	public void run() {
		String content = new String();
      		try {
			ObjectInputStream input  = new ObjectInputStream(clientSocket.getInputStream());
                        Vector contentObject  = (Vector)input.readObject();	
         		content = (String)contentObject.elementAt(0);
			if (content.length() > 0) {
	        		content = content.replace("\\", "");
        	        	List<ResolvedNamedEntity> entities = oscar.findAndResolveNamedEntities(content);
         	       		if (entities.size() > 0) {
					PrintWriter output = new PrintWriter(clientSocket.getOutputStream(), true);
					System.out.println("--------------------------------\nOutput -\n");
	        	                for (ResolvedNamedEntity namedEntity : entities) {
        	        	                String keyword = namedEntity.getSurface().replace("\"", "");
                	        	        output.println("\""+keyword+"\",\""+namedEntity.getNamedEntity().getConfidence()+"\"");
						System.out.println("\""+keyword+"\",\""+namedEntity.getNamedEntity().getConfidence()+"\"");
		                        }
					output.println("..");
					System.out.println("--------------------------------\n");
					input.close();
					output.close();
					clientSocket.close();
				}
                	}
        	}
		catch (Exception e) {
                        e.printStackTrace(System.err);
                }
   	}
}	
